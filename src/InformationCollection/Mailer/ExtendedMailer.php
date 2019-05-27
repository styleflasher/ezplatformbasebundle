<?php

namespace Styleflasher\eZPlatformBaseBundle\InformationCollection\Mailer;

use Netgen\Bundle\InformationCollectionBundle\Exception\EmailNotSentException;
use Netgen\Bundle\InformationCollectionBundle\Value\EmailData;
use Styleflasher\eZPlatformBaseBundle\InformationCollection\Value\EmailDataExtended;

class ExtendedMailer extends \Netgen\Bundle\InformationCollectionBundle\Mailer\Mailer
{

    /**
     * Create and send message supporting cc and bcc recipients
     *
     * @param EmailData $data
     */
    public function createAndSendMessage(EmailData $data)
    {
        if (!($data instanceof EmailDataExtended)) {
            return parent::createAndSendMessage($data);
        }

        $message = new \Swift_Message();

        try {
            $message->setTo($data->getRecipient());
        } catch (\Swift_RfcComplianceException $e) {
            throw new EmailNotSentException('recipient', $e->getMessage());
        }

        try {
            $message->setFrom($data->getSender());
        } catch (\Swift_RfcComplianceException $e) {
            throw new EmailNotSentException('sender', $e->getMessage());
        }

        $message->setSubject($data->getSubject());
        $message->setBody($data->getBody(), 'text/html');

        if ($data->hasAttachments()) {
            foreach ($data->getAttachments() as $attachment) {
                $message->attach(
                    \Swift_Attachment::fromPath($attachment->inputUri, $attachment->mimeType)
                        ->setFilename($attachment->fileName)
                );
            }
        }

        if (!empty($data->getBccRecipients())) {
            $message->setBcc($data->getBccRecipients());
        }

        if (!empty($data->getCcRecipients())) {
            $message->setCc($data->getCcRecipients());
        }

        if (!$this->internalMailer->send($message)) {
            throw new EmailNotSentException('send', 'invalid mailer configuration?');
        }
    }
}
