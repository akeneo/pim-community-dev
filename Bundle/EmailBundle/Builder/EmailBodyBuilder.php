<?php


namespace Oro\Bundle\EmailBundle\Builder;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;

/**
 * A helper class allows you to easy build EmailBody entity
 */
class EmailBodyBuilder
{
    /**
     * @var EmailBody
     */
    private $emailBody = null;

    /**
     * Gets built EmailBody entity
     *
     * @return EmailBody
     * @throws \LogicException
     */
    public function getEmailBody()
    {
        if ($this->emailBody === null) {
            throw new \LogicException('Call setEmailBody first.');
        }

        return $this->emailBody;
    }

    /**
     * Sets an email body properties
     *
     * @param string $content
     * @param bool $bodyIsText
     */
    public function setEmailBody($content, $bodyIsText)
    {
        $this->emailBody = new EmailBody();
        $this->emailBody
            ->setContent($content)
            ->setBodyIsText($bodyIsText);
    }

    /**
     * Adds an email attachment
     *
     * @param string $fileName
     * @param string $content
     * @param string $contentType
     * @param string $contentTransferEncoding
     * @throws \LogicException
     */
    public function addEmailAttachment(
        $fileName,
        $content,
        $contentType,
        $contentTransferEncoding
    ) {
        if ($this->emailBody === null) {
            throw new \LogicException('Call setEmailBody first.');
        }

        $emailAttachment = new EmailAttachment();
        $emailAttachmentContent = new EmailAttachmentContent();

        $emailAttachmentContent
            ->setEmailAttachment($emailAttachment)
            ->setContentTransferEncoding($contentTransferEncoding)
            ->setValue($content);

        $emailAttachment
            ->setFileName($fileName)
            ->setContentType($contentType)
            ->setContent($emailAttachmentContent);

        $this->emailBody->addAttachment($emailAttachment);
    }
}
