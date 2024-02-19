<?php

namespace App\Controller\Frontend;

use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class EmailVerifyController extends AbstractController
{
    #[Route("/email-verify", name:"frontend:email-verify")]
    public function emailVerify(
        Request $request,
        UserRepository $userRepository,
        EmailVerifier $emailVerifier
    ): Response {
        
        if ( !$request->get('id') ) {
            return $this->render( 'pages/email-verify.html.twig', ['mode'=>'invalid-request'] );
        }
        
        $user = $userRepository->find( $request->get('id') );
        if ( !$user ) {
            return $this->render( 'pages/email-verify.html.twig', ['mode'=>'user-not-found'] );
        }

        if ( $user->isEmailValidated() ) {
            return $this->render( 'pages/email-verify.html.twig', ['mode'=>'already-validated'] );
        }

        try {
            $emailVerifier->handleEmailConfirmation( $request, $user );
        } catch (VerifyEmailExceptionInterface $exception) {
            return $this->render(
                'pages/email-verify.html.twig',
                [
                    'mode' => 'validation-failed',
                    'error' => $exception->getReason()
                ]
            );
        }

        return $this->render( 'pages/email-verify.html.twig', ['mode'=>'success'] );
    }
}
