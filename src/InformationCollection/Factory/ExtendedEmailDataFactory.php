<?php

namespace Styleflasher\eZPlatformBaseBundle\InformationCollection\Factory;

use Netgen\Bundle\InformationCollectionBundle\Constants;
use Netgen\Bundle\InformationCollectionBundle\DependencyInjection\ConfigurationConstants;
use Netgen\Bundle\InformationCollectionBundle\Event\InformationCollected;
use Netgen\Bundle\InformationCollectionBundle\Exception\MissingValueException;
use Netgen\Bundle\InformationCollectionBundle\Factory\EmailDataFactory;
use Netgen\Bundle\InformationCollectionBundle\Value\TemplateData;
use Styleflasher\eZPlatformBaseBundle\InformationCollection\Value\EmailDataExtended;

class ExtendedEmailDataFactory extends EmailDataFactory
{

    /**
     * Factory method.
     *
     * @param InformationCollected $value
     *
     * @return EmailData
     */
    public function build(InformationCollected $value)
    {
        $location = $value->getLocation();
        $contentType = $value->getContentType();
        $content = $this->contentService->loadContent($location->contentId);

        $template = $this->resolveTemplate($contentType->identifier);

        $templateWrapper = $this->twig->load($template);
        $data = new TemplateData($value, $content, $templateWrapper);

        $body = $this->resolveBody($data);

        try {
            $ccRecipients = $this->resolve($data, 'cc_recipients');
            $ccRecipients = explode(",", $ccRecipients);
            if (!is_array($ccRecipients)) {
                $ccRecipients = [];
            }
        } catch (MissingValueException $e) {
            $ccRecipients = [];
        }

        try {
            $bccRecipients = $this->resolve($data, 'bcc_recipients');
            $bccRecipients = explode(",", $bccRecipients);
            if (!is_array($bccRecipients)) {
                $bccRecipients = [];
            }
        } catch (MissingValueException $e) {
            $bccRecipients = [];
        }

        return new EmailDataExtended(
            $this->resolveEmail($data, Constants::FIELD_RECIPIENT),
            $this->resolveEmail($data, Constants::FIELD_SENDER),
            $this->resolve($data, Constants::FIELD_SUBJECT),
            $body,
            $this->resolveAttachments($contentType->identifier, $value->getInformationCollectionStruct()->getCollectedFields()),
            $ccRecipients,
            $bccRecipients
        );
    }
}
