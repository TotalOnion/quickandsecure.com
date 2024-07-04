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
            $criteria = $userRepository->parseFilter( $request );
            $users = $userRepository->findBy(
                $criteria,
                $userRepository->parseOrderBy( $request ),
                $userRepository->parseLimit( $request ),
                $userRepository->parseOffset( $request )
            );
        } catch( ApiQueryStringException $e ) {
            return new JsonResponse(
                [ 'error' => $e->getMessage() ],
                Response::HTTP_BAD_REQUEST
            );
        } catch ( \Exception $e ) {
            return new JsonResponse(
                [ 'error' => $e->getMessage() ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            $users,
            Response::HTTP_OK,
            [
                'Content-Range' => $userRepository->count( $criteria ),
                'Access-Control-Expose-Headers' => 'Content-Range'
            ]
        );
    }
}
