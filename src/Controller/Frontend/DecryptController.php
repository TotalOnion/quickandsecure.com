<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DecryptController extends AbstractController
{
    #[Route("/s/{slug}", name:"frontend:decrypt", requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function decrypt(string $slug): Response {
        return $this->render( 'pages/decrypt.html.twig' );
    }
}
