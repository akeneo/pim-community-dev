<?php

namespace Oro\Bundle\EmailBundle\Builder;

use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;

class EmailEntityBuilder
{
    /**
     * @var EmailEntityBatchProcessor
     */
    private $batch;

    /**
     * @var EmailAddressManager
     */
    private $emailAddressManager;

    /**
     * Constructor
     *
     * @param EmailEntityBatchProcessor $batch
     * @param EmailAddressManager $emailAddressManager
     */
    public function __construct(EmailEntityBatchProcessor $batch, EmailAddressManager $emailAddressManager)
    {
        $this->batch = $batch;
        $this->emailAddressManager = $emailAddressManager;
    }

    /**
     * Create Email entity object
     *
     * @param string $subject The email subject
     * @param string $from The FROM email address, for example: john@example.com or "John Smith" <john@example.c4m>
     * @param string|string[]|null $to The TO email address(es).
     *                                 Example of email address see in description of $from parameter
     * @param \DateTime $sentAt The date/time when email sent
     * @param \DateTime $receivedAt The date/time when email received
     * @param \DateTime $internalDate The date/time an email server returned in INTERNALDATE field
     * @param integer $importance The email importance flag. Can be one of *_IMPORTANCE constants of Email class
     * @param string|string[]|null $cc The CC email address(es).
     *                                 Example of email address see in description of $from parameter
     * @param string|string[]|null $bcc The BCC email address(es).
     *                                  Example of email address see in description of $from parameter
     * @return Email
     */
    public function email(
        $subject,
        $from,
        $to,
        $sentAt,
        $receivedAt,
        $internalDate,
        $importance = Email::NORMAL_IMPORTANCE,
        $cc = null,
        $bcc = null
    ) {
        $result = new Email();
        $result
            ->setSubject($subject)
            ->setFromName($from)
            ->setFromEmailAddress($this->address($from))
            ->setSentAt($sentAt)
            ->setReceivedAt($receivedAt)
            ->setInternalDate($internalDate)
            ->setImportance($importance);

        $this->addRecipients($result, EmailRecipient::TO, $to);
        $this->addRecipients($result, EmailRecipient::CC, $cc);
        $this->addRecipients($result, EmailRecipient::BCC, $bcc);

        $this->batch->addEmail($result);

        return $result;
    }

    /**
     * Add recipients to the specified Email object
     *
     * @param Email $obj The Email object recipients is added to
     * @param string $type The recipient type. Can be to, cc or bcc
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     */
    protected function addRecipients(Email $obj, $type, $email)
    {
        if (!empty($email)) {
            if (is_string($email)) {
                $obj->addRecipient($this->recipient($type, $email));
            } elseif (is_array($email) || $email instanceof \Traversable) {
                foreach ($email as $e) {
                    $obj->addRecipient($this->recipient($type, $e));
                }
            }
        }
    }

    /**
     * Create EmailAddress entity object
     *
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     * @return EmailAddress
     */
    public function address($email)
    {
        $pureEmail = EmailUtil::extractPureEmailAddress($email);
        $result = $this->batch->getAddress($pureEmail);
        if ($result === null) {
            $result = $this->emailAddressManager->newEmailAddress()
                ->setEmail($pureEmail);
            $this->batch->addAddress($result);
        }

        return $result;
    }

    /**
     * Create EmailAttachment entity object
     *
     * @param string $fileName The attachment file name
     * @param string $contentType The attachment content type. It may be any MIME type
     * @return EmailAttachment
     */
    public function attachment($fileName, $contentType)
    {
        $result = new EmailAttachment();
        $result
            ->setFileName($fileName)
            ->setContentType($contentType);

        return $result;
    }

    /**
     * Create EmailAttachmentContent entity object
     *
     * @param string $content The attachment content encoded as it is specified in $contentTransferEncoding parameter
     * @param string $contentTransferEncoding The attachment content encoding type
     * @return EmailAttachmentContent
     */
    public function attachmentContent($content, $contentTransferEncoding)
    {
        $result = new EmailAttachmentContent();
        $result
            ->setValue($content)
            ->setContentTransferEncoding($contentTransferEncoding);

        return $result;
    }

    /**
     * Create EmailBody entity object
     *
     * @param string $content The body content
     * @param bool $isHtml Indicate whether the body content is HTML or TEXT
     * @param bool $persistent Indicate whether this email body can be removed by the email cache manager or not
     *                         Set false for external email, and false for system email, for example sent by BAP
     * @return EmailBody
     */
    public function body($content, $isHtml, $persistent = false)
    {
        $result = new EmailBody();
        $result
            ->setContent($content)
            ->setBodyIsText(!$isHtml)
            ->setPersistent($persistent);

        return $result;
    }

    /**
     * Create EmailFolder entity object for INBOX folder
     *
     * @param string $fullName The full name of INBOX folder if known
     * @param string $name The name of INBOX folder if known
     * @return EmailFolder
     */
    public function folderInbox($fullName = null, $name = null)
    {
        return $this->folder(
            EmailFolder::INBOX,
            $fullName !== null ? $fullName : 'Inbox',
            $name !== null ? $name : 'Inbox'
        );
    }

    /**
     * Create EmailFolder entity object for SENT folder
     *
     * @param string $fullName The full name of SENT folder if known
     * @param string $name The name of SENT folder if known
     * @return EmailFolder
     */
    public function folderSent($fullName = null, $name = null)
    {
        return $this->folder(
            EmailFolder::SENT,
            $fullName !== null ? $fullName : 'Sent',
            $name !== null ? $name : 'Sent'
        );
    }

    /**
     * Create EmailFolder entity object for TRASH folder
     *
     * @param string $fullName The full name of TRASH folder if known
     * @param string $name The name of TRASH folder if known
     * @return EmailFolder
     */
    public function folderTrash($fullName = null, $name = null)
    {
        return $this->folder(
            EmailFolder::TRASH,
            $fullName !== null ? $fullName : 'Trash',
            $name !== null ? $name : 'Trash'
        );
    }

    /**
     * Create EmailFolder entity object for DRAFTS folder
     *
     * @param string $fullName The full name of DRAFTS folder if known
     * @param string $name The name of DRAFTS folder if known
     * @return EmailFolder
     */
    public function folderDrafts($fullName = null, $name = null)
    {
        return $this->folder(
            EmailFolder::DRAFTS,
            $fullName !== null ? $fullName : 'Drafts',
            $name !== null ? $name : 'Drafts'
        );
    }

    /**
     * Create EmailFolder entity object for custom folder
     *
     * @param string $fullName The full name of the folder
     * @param string $name The name of the folder
     * @return EmailFolder
     */
    public function folderOther($fullName, $name)
    {
        return $this->folder(EmailFolder::OTHER, $fullName, $name);
    }

    /**
     * Create EmailFolder entity object
     *
     * @param string $type The folder type. Can be inbox, sent, trash, drafts or other
     * @param string $fullName The full name of a folder
     * @param string $name The folder name
     * @return EmailFolder
     */
    protected function folder($type, $fullName, $name)
    {
        $result = $this->batch->getFolder($type, $fullName);
        if ($result === null) {
            $result = new EmailFolder();
            $result
                ->setType($type)
                ->setFullName($fullName)
                ->setName($name);
            $this->batch->addFolder($result);
        }

        return $result;
    }

    /**
     * Register EmailOrigin entity object
     *
     * @param EmailFolder $folder The email folder
     * @return EmailFolder
     */
    public function setFolder(EmailFolder $folder)
    {
        $this->batch->addFolder($folder);

        return $folder;
    }

    /**
     * Register EmailOrigin entity object
     *
     * @param EmailOrigin $origin The email origin
     * @return EmailOrigin
     */
    public function setOrigin(EmailOrigin $origin)
    {
        $this->batch->addOrigin($origin);

        return $origin;
    }

    /**
     * Create EmailRecipient entity object to store TO field
     *
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     * @return EmailRecipient
     */
    public function recipientTo($email)
    {
        return $this->recipient(EmailRecipient::TO, $email);
    }

    /**
     * Create EmailRecipient entity object to store CC field
     *
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     * @return EmailRecipient
     */
    public function recipientCc($email)
    {
        return $this->recipient(EmailRecipient::CC, $email);
    }

    /**
     * Create EmailRecipient entity object to store BCC field
     *
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     * @return EmailRecipient
     */
    public function recipientBcc($email)
    {
        return $this->recipient(EmailRecipient::BCC, $email);
    }

    /**
     * Create EmailRecipient entity object
     *
     * @param string $type The recipient type. Can be to, cc or bcc
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     * @return EmailRecipient
     */
    protected function recipient($type, $email)
    {
        $result = new EmailRecipient();

        return $result
            ->setType($type)
            ->setName($email)
            ->setEmailAddress($this->address($email));
    }

    /**
     * Set this builder in initial state
     */
    public function clear()
    {
        $this->batch->clear();
    }

    /**
     * Removes all email objects from a batch processor is used this builder
     */
    public function removeEmails()
    {
        $this->batch->removeEmails();
    }

    /**
     * Get built batch contains all entities managed by this builder
     *
     * @return EmailEntityBatchInterface
     */
    public function getBatch()
    {
        return $this->batch;
    }
}
