<?php

namespace App\Services;

use App\Entity\EventLog;
use App\Entity\EventLoggableInterface;
use App\Repository\EventLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class EventLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventLogRepository $eventLogRepository,
    ) {}

    public function log(
        EventLoggableInterface $entity,
        string $eventName,
        ?array $eventData = null
    ) {
        if ( !$entity->getId() ) {
            throw new \Exception( 'EventLogService::log called with an Entity with no id. Has the entity been persisted?' );
        }

        $eventLog = new EventLog();
        $eventLog->setAssociatedEntityId( $entity->getId() );
        $eventLog->setEntityClassName( get_class($entity) );
        $eventLog->setEvent( sprintf('%s.%s', $entity->getEventLogPrefix(), $eventName ) );

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
}
