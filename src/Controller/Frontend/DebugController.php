<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DebugController extends AbstractController
{
    #[Route("/email-test", name:"debug:email-test")]
    public function emailPreview(): Response
    {
        return $this->render( 'emails/email-validation.html.twig', ['validationUrl' => '#']);
    }
}
