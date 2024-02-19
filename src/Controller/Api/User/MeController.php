<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\EmailRepository;
use App\Services\MailerService;
use App\Services\EventLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/user')]
class MeController extends AbstractController
{
    #[Route('/me', name:'api:user:me', methods:['GET'])]
    public function registrationStatus(
        #[CurrentUser] ?User $user,
        EmailRepository $emailRepository,
        EventLogService $eventLogService,
    ) {
        if ( !$user ) {
            return new Response(json_encode([
                'user-type' => 'anonymous',
                'capabilities' => User::getCapabilitiesByRole( 'PUBLIC_ACCESS' ),
            ]));
        }

        $payload = $user->jsonSerialize();
        $payload['user-type'] = 'user';
        if ( !$user->isEmailValidated() ) {
            // The user has not verified their email. Return info on where the verification email is in the process
            $latestInviteEmail = $emailRepository->findOneBy(
                [
                    'recipientUser' => $user,
                    'type' => MailerService::EMAIL_TYPE_VERIFY_EMAIL
                ],
                [ 'id' => 'DESC' ]
            );

            if ( $latestInviteEmail ) {
                $payload['invite-email-events'] = [];
                foreach ( $eventLogService->findEntityEvents( $latestInviteEmail ) as $emailEvent ) {
                    $payload['invite-email-events'][] = $emailEvent->jsonSerialize();
                }
            }
        }

        return new Response( json_encode( $payload ) );
    }
}
