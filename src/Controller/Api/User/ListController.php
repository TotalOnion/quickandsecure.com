<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/user')]
class ListController extends AbstractController
{
    #[Route('', name:'api:user:list', methods:['GET'])]
    public function get(
        #[CurrentUser] ?User $user,
        Request $request,
        UserRepository $userRepository
    ): JsonResponse
    {
        if ( ! $user ) {
            return new JsonResponse( null, RESPONSE::HTTP_UNAUTHORIZED );
        }

        if ( ! $user->hasRole( User::ROLE_SUPER_ADMIN ) ) {
            return new JsonResponse( null, RESPONSE::HTTP_FORBIDDEN );
        }

        try {
            //$users = $userRepository->findAll();
            $users = $userRepository->findBy(
                [],
                $userRepository->parseOrderBy( $request )
            );
        } catch ( \Exception $e ) {
            throw $e;
        }

        return new JsonResponse(
            $users,
            RESPONSE::HTTP_OK,
            [
                'Content-Range' => 1,
                'Access-Control-Expose-Headers' => 'Content-Range'
            ]
        );
    }
}
