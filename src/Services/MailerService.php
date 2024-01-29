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

        $email = (new TemplatedEmail())
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

        $email->getHeaders()->addTextHeader( 'h:X-Mailgun-Variables', json_encode( $mailgunVariables ) );

        $this->mailerInterface->send( $email );

        /*
            TODO:
                - Save the uuid, recipient user ID, and email type to an Email entity
                - Save an EmailEvent to say it has been dispatched
        */
        // Create an entry in the emails table
        $email = new Email();
        $email->setRecipientUser( $recipientUser );
        $email->setIdentifier( $emailIdentifier );
        $email->settype( $emailType );
        $this->entityManager->persist( $email );

        // create an emailEvent entry
        $emailEvent = new EmailEvent();

        $this->entityManager->flush( $email );

        return $email;
    }

    private function logEvent( string $uuid, User $recipientUser, string $emailType )
    {
        
    }
}
