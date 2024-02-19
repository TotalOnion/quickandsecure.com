<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Services\MailerService;
use App\Services\EventLogService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/user')]
class RegisterController extends AbstractController
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
        EmailVerifier $emailVerifier,
        EventLogService $eventLogService,
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

        $eventLogService->log( $user, User::EVENT_REGISTERED );

        $mailerService->sendToUser(
            MailerService::EMAIL_TYPE_VERIFY_EMAIL,
            $user,
            'Please verify your email address.',
            'emails/email-validation.html.twig',
            $emailVerifier->getEmailConfirmationContext(
                'frontend:email-verify',
                $user
            )
        );

        return $authenticationSuccessHandler->handleAuthenticationSuccess($user);
    }
}
