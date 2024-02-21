<?php

namespace App\Controller\Api\Secret;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/secret')]
class ListController extends AbstractController
{
    #[Route('', name:'api:secret:list', methods:['GET'])]
    public function get(
        #[CurrentUser] ?User $user,
    ): Response
    {
        if ( ! $user ) {
            return new Response(null, RESPONSE::HTTP_UNAUTHORIZED);
        }

        $secrets = [];
        foreach ( $user->getCreatedSecrets() as $secret ) {
            $secrets[] = $secret->jsonSerialize();
        }

        return new Response(json_encode($secrets), RESPONSE::HTTP_OK);
    }
}
