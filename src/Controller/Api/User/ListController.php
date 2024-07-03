<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Exception\ApiQueryStringException;
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
            $users = $userRepository->findBy(
                [],
                $userRepository->parseOrderBy( $request )
            );
        } catch( ApiQueryStringException $e ) {
            return new JsonResponse(
                [ 'error' => $e->getMessage() ],
                Response::HTTP_BAD_REQUEST
            );
        } catch ( \Exception $e ) {
            throw $e;
        }

        return new JsonResponse(
            $users,
            Response::HTTP_OK,
            [
                'Content-Range' => count( $users ),
                'Access-Control-Expose-Headers' => 'Content-Range'
            ]
        );
    }
}
