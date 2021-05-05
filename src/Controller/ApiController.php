<?php

namespace App\Controller;

use App\Entity\Secret;
use App\Repository\SecretRepository;
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
        SecretRepository $secretRepository,
        Request $request,
        string $slug
    ): Response
    {
        $payload = json_decode($request->getContent());

        if (
            json_last_error() !== JSON_ERROR_NONE
            || !property_exists($payload, 'data')
            || !property_exists($payload, 'iv')
        ) {
            // bad or missing required data
            return new Response(null, RESPONSE::HTTP_BAD_REQUEST);
        }

        if ($secretRepository->findOneBySlug($slug)) {
            // to the surprise of the entire universe, that slug already exists
            return new Response(null, RESPONSE::HTTP_CONFLICT);
        }

        try {
            $secret = new Secret();
            $secret->setSlug($slug);
            $secret->setData($payload->data);
            $secret->setIv($payload->iv);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($secret);
            $entityManager->flush();

            return new Response(null, RESPONSE::HTTP_CREATED);
        } catch (\Exception $e) {
            return new Response(null, RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
