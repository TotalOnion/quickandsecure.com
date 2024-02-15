<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\EmailRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/user')]
class UserController extends AbstractController
{
    #[Route('/register', name:'api:user:register', methods:['POST'])]
    public function register(
        #[CurrentUser] ?User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Request $request,
        MailerService $mailerService,
        AuthenticationSuccessHandler $authenticationSuccessHandler,
        EmailVerifier $emailVerifier
    ): Response
    {
        if ( $user ) {
            // Registration is not available to logged in users, obvs
            return new Response(
                json_encode(['message'=>'You are currently logged in. Registration is not permitted.']),
                Response::HTTP_FORBIDDEN
            );
        }

        $payload = json_decode($request->getContent());

        if (
            json_last_error() !== JSON_ERROR_NONE
            || !property_exists($payload, 'username')
            || !property_exists($payload, 'password')
        ) {
            // bad or missing required data
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $userExists = $userRepository->findOneBy(['email' => $payload->username]);
        if ( $userExists ) {
            return new Response(
                json_encode(['message' => 'Specified email address already exists.' ]),
                RESPONSE::HTTP_CONFLICT
            );
        }

        $user = new User();
        $user->setEmail( $payload->username );
        
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $payload->password
        );
        $user->setPassword( $hashedPassword );
        $user->setEmailValidated( false );
        $entityManager->persist( $user );
        $entityManager->flush();

        $signatureComponents = $emailVerifier->getEmailConfirmationContext(
            'frontend:email-verify',
            $user
        );

        $mailerService->sendToUser(
            MailerService::EMAIL_TYPE_VERIFY_EMAIL,
            $user,
            'Please verify your email address.',
            'emails/email-validation.html.twig',
            $signatureComponents
        );

        return $authenticationSuccessHandler->handleAuthenticationSuccess($user);
    }

    #[Route('/me', name:'api:user:me', methods:['GET'])]
    public function registrationStatus(
        #[CurrentUser] ?User $user,
        EmailRepository $emailRepository
    ) {
        if ( !$user ) {
            return new Response(json_encode([
                'user-type' => 'anonmymous'
            ]));
        }

        $payload = $user->jsonSerialize();
        $payload['user-type'] = 'user';
        if ( !$user->isEmailValidated() ) {
            // The user has not verified their email. Return info on where the verification email is in the process
            $latestInviteEmail = $emailRepository->findOneBy(
                [
                    'recipientUser' => $user,
                    'type' => MailerService::EMAIL_TYPE_VERIFY_EMAIL
                ],
                [
                    'id' => 'DESC'
                ]
            );
            $payload['invite-email-events'] = [];
            foreach ( $latestInviteEmail->getEmailEvents() as $emailEvent ) {
                $payload['invite-email-events'][] = $emailEvent->jsonSerialize();
            }
        }

        $payload['roles'] = $user->getRoles();

        return new Response(json_encode($payload));
    }
}
