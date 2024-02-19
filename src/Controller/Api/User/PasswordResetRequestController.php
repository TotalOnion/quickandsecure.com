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
class PasswordResetRequestController extends AbstractController
{
    #[Route('/password-reset-request', name:'api:user:password-reset-request', methods:['POST'])]
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
            // Logged in users don't need this endpoint as they can set a new password using the user/change-password endpoint
            return new Response(
                json_encode(['message'=>'You are currently logged in. You can change your password from the account settings page.']),
                Response::HTTP_FORBIDDEN
            );
        }

        $payload = json_decode($request->getContent());

        if (
            json_last_error() !== JSON_ERROR_NONE
            || !property_exists($payload, 'username')
        ) {
            // bad or missing required data
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $payload->username]);
        if ( !$user ) {
            return new Response(
                json_encode(['message' => 'Specified email address not found. Check the supplied email address, or register for a new account.' ]),
                RESPONSE::HTTP_CONFLICT
            );
        }

        $eventLogService->log( $user, User::EVENT_PASSWORD_RESET_REQUESTED );

        $mailerService->sendToUser(
            MailerService::EMAIL_TYPE_PASSWORD_RESET_REQUEST,
            $user,
            'Password reset requested.',
            'emails/password-reset-request.html.twig',
            $emailVerifier->getEmailConfirmationContext(
                'frontend:password-reset',
                $user
            )
        );

        return new Response();
    }
}
