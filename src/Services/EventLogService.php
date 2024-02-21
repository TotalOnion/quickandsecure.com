<?php

namespace App\Services;

use App\Entity\EventLog;
use App\Entity\EventLoggableInterface;
use App\Repository\EventLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventLogRepository $eventLogRepository,
    ) {}

    public function log(
        EventLoggableInterface $entity,
        string $eventName,
        array $eventData = [],
        ?Request $associatedRequest = null,
    ) {
        if ( !$entity->getId() ) {
            throw new \Exception( 'EventLogService::log called with an Entity with no id. Has the entity been persisted?' );
        }

        $eventLog = new EventLog();
        $eventLog->setAssociatedEntityId( $entity->getId() );
        $eventLog->setEntityClassName( get_class($entity) );
        $eventLog->setEvent( sprintf('%s.%s', $entity->getEventLogPrefix(), $eventName ) );

        if ( $associatedRequest ) {
            $eventData['request-data'] = [
                'user-agent' => $associatedRequest->headers->get('User-Agent'),
                'ip-address' => $associatedRequest->getClientIp(),
            ];
        }

        if ( $eventData ) {
            $eventLog->setEventData( $eventData );
        }

        $this->entityManager->persist( $eventLog );
        $this->entityManager->flush();
    }

    public function findEntityEvents( EventLoggableInterface $entity )
    {
        return $this->eventLogRepository->findBy([
            'associatedEntityId' => $entity->getId(),
            'entityClassName' => get_class( $entity )
        ]);
    }

    public function getRequestDataForLog( $request )
    {

    }
}
