<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route("/", name:"frontend:homepage")]
    public function homepage(): Response
    {
        return $this->render('pages/index.html.twig');
    }
}
