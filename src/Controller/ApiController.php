<?php

namespace App\Controller;

use App\Entity\Secret;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/secret/{slug}", name="secret", methods={"POST"}, requirements={"slug"="^[a-zA-Z0-9]{7}$"})
     */
    public function create(
        Request $request,
        string $slug
    ): Response
    {
        $secret = new Secret();
        $secret->setSlug($slug);
        $secret->setData($request->getContent());

        return new Response(null, RESPONSE::HTTP_CREATED);
    }
}
