<?php

namespace App\Controller\Api\Secret;

use App\Entity\Secret;
use App\Entity\User;
use App\Repository\SecretRepository;
use App\Services\EventLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/secret')]
class CreateController extends AbstractController
{
    #[Route('/{slug}', name:'api:secret:create', methods:['POST'], requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function create(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
        SecretRepository $secretRepository,
        Request $request,
        EventLogService $eventLogService,
        string $slug,
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

            $eventLogService->log( $secret, Secret::EVENT_CREATED );

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
}
