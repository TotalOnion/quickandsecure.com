<?php

namespace App\Controller\Api;

use App\Entity\Secret;
use App\Entity\User;
use App\Repository\SecretRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/secret')]
class SecretController extends AbstractController
{
    #[Route('/{slug}', name:'api:create_secret', methods:['POST'], requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function create(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
        SecretRepository $secretRepository,
        Request $request,
        string $slug
    ): Response
    {
        $payload = json_decode($request->getContent());

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new Response(null, RESPONSE::HTTP_BAD_REQUEST);
        }

        if ( !$this->payloadIsValid($user, $payload) ) {
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

            if ($user) {
                $secret->setCreatedBy( $user );
                $secret->setDescription( $payload->description );
                $secret->setMustBeLoggedInToRead( $payload->mustBeLoggedInToView ? true : false );
            } else {
                $secret->setMustBeLoggedInToRead(false);
            }

            $entityManager->persist($secret);
            $entityManager->flush();

            return new Response(null, RESPONSE::HTTP_CREATED);
        } catch (\Exception $e) {
            return new Response(null, RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function payloadIsValid( ?User $user, \stdClass $payload ):bool
    {
        // data and iv are required, even for anonymous secrets
        if (
            !property_exists($payload, 'data')
            || !property_exists($payload, 'iv')
            || !preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $payload->data)
            || !preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $payload->iv)
        ) {
            return false;
        }

        // Logged in users must also send description, and mustBeLoggedInToView (even if they are empty)
        if (
            $user
            && (
                !property_exists($payload, 'description')
                || !property_exists($payload, 'mustBeLoggedInToView')
            )
        ) {
            return false;
        }

        return true;
    }

    #[Route('/{slug}', name:'api:get_secret', methods:['GET'], requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function get(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
        SecretRepository $secretRepository,
        string $slug
    ): Response
    {
        $secret = $secretRepository->findOneBySlug($slug);
        if (!$secret) {
            return new Response(null, RESPONSE::HTTP_NOT_FOUND);
        }

        if ( $secret->getDestroyedOn() ) {
            $responseData = [
                'message' => sprintf('This secret was read, and destroyed on %s UTC.', $secret->getDestroyedOn()->format('Y-m-d h:i:s'))
            ];
            return new Response(json_encode($responseData), RESPONSE::HTTP_GONE);
        }

        if ( $secret->isMustBeLoggedInToRead() && !$user ) {
            $responseData = [
                'message' => sprintf('The sender of this secret, %s, requires you to be logged in to read it. Login or create an account now.', $secret->getCreatedBy()->getEmail())
            ];
            return new Response(json_encode($responseData), RESPONSE::HTTP_UNAUTHORIZED);
        }

        $responseData = [
            'data' => $secret->getData(),
            'iv' => $secret->getIv(),
        ];

        $secret->setDestroyedOn( new \DateTime() );
        $secret->setData( null );
        $secret->setIv( null );
        $secret->setReadBy( $user );
        $entityManager->persist( $secret );
        $entityManager->flush();

        return new Response(json_encode($responseData), RESPONSE::HTTP_OK);
    }
}
