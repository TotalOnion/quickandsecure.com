<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/api/v1')]
class PreflightController extends AbstractController
{
    #[Route('/{namespace}', name:'api:preflight:namespace', methods:['OPTIONS'])]
    #[Route('/{namespace}/{object}', name:'api:preflight:namespace:object', methods:['OPTIONS'])]
    #[Route('/{namespace}/{object}/{action}', name:'api:preflight:namespace:object:action', methods:['OPTIONS'])]
    public function loginPreflight(
        Request $request,
        RouterInterface $router,
        string $namespace,
        ?string $object = null,
        ?string $action = null,
    ) {
        $response = new Response(null, Response::HTTP_OK );
        $response->headers->set('Access-Control-Allow-Headers','*');
        $response->headers->set('Access-Control-Allow-Methods','OPTIONS,POST,GET');
        return $response;
    }
}
