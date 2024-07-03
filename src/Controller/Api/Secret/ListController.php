<?php

namespace App\Controller\Api\Secret;

use App\Entity\Secret;
use App\Entity\User;
use App\Exception\ApiQueryStringException;
use App\Repository\SecretRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/secret')]
class ListController extends AbstractController
{
    #[Route('', name:'api:secret:list', methods:['GET'])]
    public function get(
        #[CurrentUser] ?User $user,
        Request $request,
        SecretRepository $secretRepository
    ): JsonResponse
    {
        if ( ! $user ) {
            return new JsonResponse(null, RESPONSE::HTTP_UNAUTHORIZED);
        }

        try {
            $secrets = $secretRepository->findBy(
                [
                    'createdBy' => $user->getId()
                ],
                $this->parseSort( $request, Secret::class )
            );
        } catch( ApiQueryStringException $e ) {
            return new JsonResponse(
                [ 'error' => $e->getMessage() ],
                RESPONSE::HTTP_BAD_REQUEST
            );
        }

        $secretsData = [];
        foreach ( $secrets as $secret ) {
            $secretsData[] = $secret->jsonSerialize();
        }

        return new JsonResponse(
            $secretsData,
            RESPONSE::HTTP_OK,
            [
                'Content-Range' => count( $secrets ),
                'Access-Control-Expose-Headers' => 'Content-Range'
            ]
        );
    }

    private function parseSort( Request $request, string $relatedEntityClass ): array
    {
        if ( $request->get('sort') ) {
            list($column,$direction)  = json_decode( $request->get('sort') );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                throw new ApiQueryStringException('Invalid json data in sort= query string.');
            }
            return [ $column => $direction ?? 'ASC' ];
        } else {
            return $relatedEntityClass::DEFAULT_SORT;
        }
    }
}
