<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route("/email-test", name:"debug:email-test")]
    public function emailPreview(): Response
    {
        return $this->render( 'emails/email-validation.html.twig', ['validationUrl' => '#']);
    }
}
