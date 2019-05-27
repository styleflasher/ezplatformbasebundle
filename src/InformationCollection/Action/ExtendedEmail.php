<?php

namespace Styleflasher\eZPlatformBaseBundle\InformationCollection\Action;

use Netgen\Bundle\InformationCollectionBundle\Action\ActionInterface;
use Netgen\Bundle\InformationCollectionBundle\Event\InformationCollected;
use Netgen\Bundle\InformationCollectionBundle\Mailer\MailerInterface;
use Styleflasher\eZPlatformBaseBundle\InformationCollection\Factory\ExtendedEmailDataFactory;
use Styleflasher\eZPlatformBaseBundle\InformationCollection\Mailer\ExtendedMailer;

class ExtendedEmail implements ActionInterface
{

    private $mailer;

    private $factory;

    public function __construct(
        ExtendedEmailDataFactory $factory,
        ExtendedMailer $mailer
    ) {

        $this->factory = $factory;
        $this->mailer = $mailer;
    }

    /**
     * @param InformationCollected $event
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function act(InformationCollected $event)
    {
        $emailData = $this->factory->build($event);

        try {
            $this->mailer->createAndSendMessage($emailData);
        } catch (EmailNotSentException $e) {
            throw new ActionFailedException('extended_email', $e->getMessage());
        }
    }
}
