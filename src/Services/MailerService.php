<?php

namespace App\Services;

use App\Entity\Email;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Services\EventLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Uid\Uuid;

class MailerService
{
    const EMAIL_TYPE_VERIFY_EMAIL           = 'EMAIL_TYPE_VERIFY_EMAIL';
    const EMAIL_TYPE_PASSWORD_RESET_REQUEST = 'EMAIL_TYPE_PASSWORD_RESET_REQUEST';

    const EMAIL_FROM_NAME    = 'Here, have this';
    const EMAIL_FROM_ADDRESS = 'hello@herehaveth.is';

    // All other email events come from the Mailgun Webhook
    const EMAIL_EVENT_DISPATCHED = 'dispatched';

    public function __construct(
        private MailerInterface $mailerInterface,
        private EntityManagerInterface $entityManager,
        private EmailVerifier $emailVerifier,
        private EventLogService $eventLogService,
    ) { }

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

        $this->eventLogService->log( $email, self::EMAIL_EVENT_DISPATCHED );

        return $email;
    }
}