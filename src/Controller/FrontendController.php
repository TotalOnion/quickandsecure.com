<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class FrontendController extends AbstractController
{
    #[Route("/", name:"frontend:homepage")]
    public function homepage(): Response
    {
        return $this->render('pages/index.html.twig');
    }

    #[Route("/s/{slug}", name:"frontend:decrypt", requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function decrypt(string $slug): Response {
        return $this->render( 'pages/decrypt.html.twig' );
    }

    #[Route("/email-verify", name:"frontend:email-verify")]
    public function emailVerify(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
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

    #[Route("/email-test", name:"debug:email-test")]
    public function emailPreview(): Response
    {
        return $this->render( 'emails/email-validation.html.twig', ['validationUrl' => '#']);
    }
}
