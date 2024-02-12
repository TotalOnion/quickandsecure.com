<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\EmailRepository;
use App\Repository\UserRepository;
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
    #[Route('/register', name:'api:user_register', methods:['POST'])]
    public function register(
        #[CurrentUser] ?User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Request $request,
        MailerService $mailerService,
        AuthenticationSuccessHandler $authenticationSuccessHandler
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

        $validationToken = base64_encode( openssl_random_pseudo_bytes(32) );
        $user->setEmailValidationToken( $validationToken );
        $user->isEmailValidated( false );
        $entityManager->persist( $user );
        $entityManager->flush();

        $email = $mailerService->sendToUser(
            MailerService::EMAIL_TYPE_VERIFY_EMAIL,
            $user,
            'Please verify your email address.',
            'emails/email-validation.html.twig',
            [
                'validationUrl' =>  sprintf(
                    'https://%s/email-verify?token=%s',
                    $_SERVER['SERVER_NAME'],
                    urlencode( $validationToken )
                )
            ]
        );

        return $authenticationSuccessHandler->handleAuthenticationSuccess($user);
    }

    #[Route('/registration-status', name:'api:registration_status', methods:['GET'])]
    public function registrationStatus(
        #[CurrentUser] ?User $user,
        EmailRepository $emailRepository
    ) {
        if ( $user->isEmailValidated() ) {
            return new Response(
                json_encode(['success'=>true])
            )
        }
        $inviteEmail = $emailRepository->findOneBy(
            [
                'recipientUser' => $user,
                'type' => MailerService::EMAIL_TYPE_VERIFY_EMAIL
            ],
            [
                'id' => 'DESC'
            ]
        );
    }
}
