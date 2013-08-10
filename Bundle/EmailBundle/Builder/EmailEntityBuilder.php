<?php

namespace Oro\Bundle\EmailBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;

class EmailEntityBuilder
{
    /**
     * @var EmailAddressManager
     */
    private $emailAddressManager;

    /**
     * @var Email
     */
    protected $email;

    /**
     * @var EmailOwnerInterface[]
     */
    protected $owners;

    /**
     * Constructor
     *
     * @param EmailAddressManager $emailAddressManager
     */
    public function __construct(EmailAddressManager $emailAddressManager)
    {
        $this->emailAddressManager = $emailAddressManager;
    }

    /**
     * Create Email entity object
     *
     * @param string $subject The email subject
     * @param string $from The FROM email address, for example: john@example.com or "John Smith" <john@example.c4m>
     * @param string|string[]|null $to The TO email address(es). Example of email address see in description of $from parameter
     * @param \DateTime $sentAt The date/time when email sent
     * @param \DateTime $receivedAt The date/time when email received
     * @param integer $importance The email importance flag. Can be one of *_IMPORTANCE constants of Email class
     * @param string|string[]|null $cc The CC email address(es). Example of email address see in description of $from parameter
     * @param string|string[]|null $bcc The BCC email address(es). Example of email address see in description of $from parameter
     * @return Email
     */
    public function email($subject, $from, $to, $sentAt, $receivedAt, $importance = Email::NORMAL_IMPORTANCE, $cc = null, $bcc = null)
    {
        $result = new Email();
        $result
            ->setSubject($subject)
            ->setFromName($from)
            ->setFromEmailAddress($this->address($from))
            ->setSentAt($sentAt)
            ->setReceivedAt($receivedAt)
            ->setImportance($importance);

        $this->addRecipients($result, EmailRecipient::TO, $to);
        $this->addRecipients($result, EmailRecipient::CC, $to);
        $this->addRecipients($result, EmailRecipient::BCC, $to);

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
        return $this->emailAddressManager->newEmailAddress()
            ->setEmail(EmailUtil::extractPureEmailAddress($email));
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
     * @param string $name The name of INBOX folder if known
     * @return EmailFolder
     */
    public function folderInbox($name = null)
    {
        return $this->folder(EmailFolder::INBOX, $name !== null ? 'Inbox' : $name);
    }

    /**
     * Create EmailFolder entity object for SENT folder
     *
     * @param string $name The name of SENT folder if known
     * @return EmailFolder
     */
    public function folderSent($name = null)
    {
        return $this->folder(EmailFolder::SENT, $name !== null ? 'Sent' : $name);
    }

    /**
     * Create EmailFolder entity object for TRASH folder
     *
     * @param string $name The name of TRASH folder if known
     * @return EmailFolder
     */
    public function folderTrash($name = null)
    {
        return $this->folder(EmailFolder::TRASH, $name !== null ? 'Trash' : $name);
    }

    /**
     * Create EmailFolder entity object for DRAFTS folder
     *
     * @param string $name The name of DRAFTS folder if known
     * @return EmailFolder
     */
    public function folderDrafts($name = null)
    {
        return $this->folder(EmailFolder::DRAFTS, $name !== null ? 'Drafts' : $name);
    }

    /**
     * Create EmailFolder entity object for custom folder
     *
     * @param string $name The name of the folder
     * @return EmailFolder
     */
    public function folderOther($name)
    {
        return $this->folder(EmailFolder::OTHER, $name);
    }

    /**
     * Create EmailFolder entity object
     *
     * @param string $type The folder type. Can be inbox, sent, trash, drafts or other
     * @param string $name The folder name
     * @return EmailFolder
     */
    protected function folder($type, $name)
    {
        $result = new EmailFolder();
        $result
            ->setType($type)
            ->setName($name);

        return $result;
    }

    /**
     * Create EmailOrigin entity object
     *
     * @param string $name The email origin name
     * @return EmailOrigin
     */
    public function origin($name)
    {
        $result = new EmailOrigin();
        $result->setName($name);

        return $result;
    }

    /**
     * Create EmailRecipient entity object to store TO field
     *
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     * @return EmailRecipient
     */
    public function toRecipient($email)
    {
        return $this->recipient(EmailRecipient::TO, $email);
    }

    /**
     * Create EmailRecipient entity object to store CC field
     *
     * @param string $email The email address, for example: john@example.com or "John Smith" <john@example.com>
     * @return EmailRecipient
     */
    public function ccRecipient($email)
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
}
