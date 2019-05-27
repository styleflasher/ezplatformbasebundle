<?php
/**
 *
 * Author: René Hrdina, styleflasher GmbH
 * Date: 06.09.18
 * Time: 13:35
 */

namespace Styleflasher\eZPlatformBaseBundle\InformationCollection\Value;

use Netgen\Bundle\InformationCollectionBundle\Value\EmailData;

class EmailDataExtended extends EmailData
{

    protected $ccRecipients;
    protected $bccRecipients;

    public function __construct($recipient, $sender, $subject, $body, array $attachments = null, array $ccRecipients = null, array $bccRecipients = null)
    {
        $this->ccRecipients = $ccRecipients;
        $this->bccRecipients = $bccRecipients;

        parent::__construct($recipient, $sender, $subject, $body, $attachments);
    }

    /**
     * @return array|null
     */
    public function getCcRecipients()
    {
        return $this->ccRecipients;
    }

    /**
     * @return array|null
     */
    public function getBccRecipients()
    {
        return $this->bccRecipients;
    }
}
