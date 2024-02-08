<?php

namespace App\Webhook;

use App\Repository\EmailRepository;
use App\Entity\EmailEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerDeliveryEvent;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerEngagementEvent;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('mailer_mailgun')]
class MailgunListener implements ConsumerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailRepository $emailRepository
    ) { }

    public function consume(RemoteEvent $event): void
    {
        if (
            $event instanceof MailerDeliveryEvent
            || $event instanceof MailerEngagementEvent
        ) {
            $metadata = $event->getMetadata();
            if(
                !is_array($metadata)
                || !$metadata
                || !array_key_exists( 'email-identifier', $metadata )
            ) {
                // it's either testing data that's been binned, or for the wrong env.
                // Just return for now rather than throwing an exception otherwise MailGun will just keep resending.
                //throw new Exception('Mailgun webhook event received with no metadata[email-identifier]');
                return;
            }

            $email = $this->emailRepository->findOneBy(['identifier' => $metadata['email-identifier']]);
            if ( !$email ) {
                // it's either testing data that's been binned, or for the wrong env.
                // Just return for now rather than throwing an exception otherwise MailGun will just keep resending.
                return;
                /*
                throw new Exception(
                    sprintf(
                        'No email found for identifier %s. Full payload: %s',
                        $metadata['email-identifier'],
                        json_encode( $metadata )
                    )
                );*/
            }

            $emailEvent = new EmailEvent;
            $emailEvent->setEmail( $email );
            $emailEvent->setTimestamp( new DateTimeImmutable() );
            $emailEvent->setEvent( $event->getName() );
            $this->entityManager->persist( $emailEvent );
            $this->entityManager->flush();
        } else {
            // This is not an email event
            return;
        }        
    }
}
