<?php

namespace App\Controller\Frontend;

use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class PasswordResetController extends AbstractController
{
    #[Route("/password-reset", name:"frontend:password-reset")]
    public function passwordReset(
        Request $request,
        UserRepository $userRepository,
        EmailVerifier $emailVerifier
    ): Response {
        
        if ( !$request->get('id') ) {
            return $this->render( 'pages/password-reset.html.twig', ['mode'=>'invalid-request'] );
        }
        
        $user = $userRepository->find( $request->get('id') );
        if ( !$user ) {
            return $this->render( 'pages/password-reset.html.twig', ['mode'=>'user-not-found'] );
        }

        try {
            $emailVerifier->handleEmailConfirmation( $request, $user );
        } catch (VerifyEmailExceptionInterface $exception) {
            return $this->render(
                'pages/password-reset.html.twig',
                [
                    'mode' => 'validation-failed',
                    'error' => $exception->getReason()
                ]
            );
        }

        return $this->render( 'pages/password-reset.html.twig', ['mode'=>'ready'] );
    }
}
