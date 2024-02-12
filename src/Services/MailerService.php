<?php

namespace App\Services;

use App\Entity\Email;
use App\Entity\EmailEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Uid\Uuid;

class MailerService
{
    const EMAIL_TYPE_VERIFY_EMAIL = 'EMAIL_TYPE_VERIFY_EMAIL';

    const EMAIL_FROM_NAME = 'Here, have this';
    const EMAIL_FROM_ADDRESS = 'hello@herehaveth.is';

    const EMAIL_EVENT_DISPATCHED = 'EMAIL_EVENT_DISPATCHED';

    public function __construct(
        private MailerInterface $mailerInterface,
        private EntityManagerInterface $entityManager,
    ) {

    }

    public function sendToUser(
        string $emailType,
        User $recipientUser,
        string $subject,
        string $template,
        ?array $templateData
    ): Email {
        $emailIdentifier = Uuid::v4();

        // Create an entry in the emails table
        $email = new Email();
        $email->setRecipientUser( $recipientUser );
        $email->setIdentifier( $emailIdentifier );
        $email->settype( $emailType );
        $this->entityManager->persist( $email );
        $this->entityManager->flush( $email );

        // Create the email
        $templatedEmail = (new TemplatedEmail())
            ->from( new Address( self::EMAIL_FROM_ADDRESS, self::EMAIL_FROM_NAME ))
            ->to( new Address( $recipientUser->getEmail() ) )
            ->subject( $subject )
            ->htmlTemplate( $template )
            ->context( $templateData )
        ;

        $mailgunVariables = [
            'email-identifier' => $emailIdentifier,
            'email-type' => $emailType,
            'recipient-user' => $recipientUser->getId()
        ];

        $templatedEmail->getHeaders()->addTextHeader( 'h:X-Mailgun-Variables', json_encode( $mailgunVariables ) );
        $templatedEmail->getHeaders()->addTextHeader( 'X-Mailgun-Tag', $emailType );
        $this->mailerInterface->send( $templatedEmail );

        // create an emailEvent entry
        $this->logEvent( $email, self::EMAIL_EVENT_DISPATCHED );

        return $email;
    }

    private function logEvent( Email $email, string $eventName )
    {
        $emailEvent = new EmailEvent();
        $emailEvent->setEmail( $email );
        $emailEvent->setTimestamp( new \DateTimeImmutable() );
        $emailEvent->setEvent( $eventName );
        $this->entityManager->persist( $emailEvent );

        $this->entityManager->flush( $email );
    }
}
