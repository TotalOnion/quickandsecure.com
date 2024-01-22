<?php

namespace App\Controller;

use App\Repository\SecretRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends AbstractController
{
    #[Route("/")]
    public function homepage(): Response
    {
        return $this->render('pages/index.html.twig');
    }

    #[Route("/s/{slug}", name:"decrypt", requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function decrypt(string $slug): Response {
        return $this->render( 'pages/decrypt.html.twig' );
    }
}
