<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        EntityManagerInterface $entityManager
    ): Response {
        
        $token = $request->get('token');
        if ( !$token ) {
            return $this->render( 'pages/email-verify.html.twig', ['mode'=>'invalid-request'] );
        }
        
        $user = $userRepository->findOneBy([ 'emailValidationToken' => $token ]);
        if ( !$user ) {
            return $this->render( 'pages/email-verify.html.twig', ['mode'=>'token-not-found'] );
        }

        if ( $user->isEmailValidated() ) {
            return $this->render( 'pages/email-verify.html.twig', ['mode'=>'already-validated'] );
        }

        $user->setEmailValidated( true );
        $entityManager->persist( $user );
        $entityManager->flush();

        return $this->render( 'pages/email-verify.html.twig', ['mode'=>'success'] );
    }

    #[Route("/email-test", name:"debug:email-test")]
    public function emailPreview(): Response
    {
        return $this->render( 'emails/email-validation.html.twig', ['validationUrl' => '#']);
    }
}
