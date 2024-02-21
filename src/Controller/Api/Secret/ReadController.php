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
class ReadController extends AbstractController
{
    #[Route('/{slug}', name:'api:secret:read', methods:['GET'], requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function get(
        #[CurrentUser] ?User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        SecretRepository $secretRepository,
        EventLogService $eventLogService,
        string $slug,
    ): Response
    {
        $secret = $secretRepository->findOneBySlug($slug);
        if (!$secret) {
            return new Response(null, RESPONSE::HTTP_NOT_FOUND);
        }

        $eventLogService->log(
            $secret,
            Secret::EVENT_READ_REQUESTED,
            [],
            $request
        );

        if ( $secret->getDestroyedOn() ) {
            $eventLogService->log(
                $secret,
                Secret::EVENT_READ_DENIED,
                [ 'reason' => Secret::DENIED_REASON_DESTROYED ],
                $request
            );
            $responseData = [
                'message' => sprintf('This secret was read, and destroyed on %s UTC.', $secret->getDestroyedOn()->format('Y-m-d h:i:s'))
            ];
            return new Response(json_encode($responseData), RESPONSE::HTTP_GONE);
        }

        if ( $secret->isMustBeLoggedInToRead() && !$user ) {
            $eventLogService->log(
                $secret,
                Secret::EVENT_READ_DENIED,
                [ 'reason' => Secret::DENIED_REASON_NOT_LOGGED_IN ],
                $request
            );
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

        $eventLogService->log( $secret, Secret::EVENT_READ_OK, [], $request );
        $eventLogService->log( $secret, Secret::EVENT_DESTROYED, [], $request );

        return new Response(json_encode($responseData), RESPONSE::HTTP_OK);
    }
}
