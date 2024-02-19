<?php

namespace App\Webhook;

use App\Repository\EmailRepository;
use App\Services\EventLogService;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerDeliveryEvent;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerEngagementEvent;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('mailer_mailgun')]
class MailgunListener implements ConsumerInterface
{
    public function __construct(
        private EventLogService $eventLogService,
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
            }

            $this->eventLogService->log( $email, $event->getName() );
        }
    }
}
