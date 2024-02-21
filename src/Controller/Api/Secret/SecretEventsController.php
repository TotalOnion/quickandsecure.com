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
class SecretEventsController extends AbstractController
{
    #[Route('/{slug}/events', name:'api:secret:events', methods:['GET'], requirements:['slug'=>'^[a-zA-Z0-9]{7}$'])]
    public function get(
        #[CurrentUser] ?User $user,
        SecretRepository $secretRepository,
        EventLogService $eventLogService,
        string $slug,
    ): Response
    {
        $secret = $secretRepository->findOneBy([
            'slug'      => $slug,
            'createdBy' => $user,
        ]);

        if (!$secret) {
            return new Response(null, RESPONSE::HTTP_NOT_FOUND);
        }

        $eventLogs = $eventLogService->findEntityEvents( $secret );

        return new Response(json_encode($eventLogs), RESPONSE::HTTP_OK);
    }
}
