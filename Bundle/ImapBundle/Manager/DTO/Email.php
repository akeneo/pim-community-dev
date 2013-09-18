<?php

namespace Oro\Bundle\ImapBundle\Manager\DTO;

use Oro\Bundle\ImapBundle\Mail\Storage\Body;
use Oro\Bundle\ImapBundle\Mail\Storage\Message;
use Oro\Bundle\ImapBundle\Mail\Storage\Exception\InvalidBodyFormatException;

class Email
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * @var ItemId
     */
    protected $id;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string[]
     */
    protected $toRecipients = array();

    /**
     * @var string[]
     */
    protected $ccRecipients = array();

    /**
     * @var string[]
     */
    protected $bccRecipients = array();

    /**
     * @var \DateTime
     */
    protected $receivedAt;

    /**
     * @var \DateTime
     */
    protected $sentAt;

    /**
     * -1 = LOW, 0 = NORMAL, 1 = HIGH
     *
     * @var integer
     */
    protected $importance;

    /**
     * @var \DateTime
     */
    protected $internalDate;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $xMessageId;

    /**
     * @var string
     */
    protected $xThreadId;

    /**
     * @var EmailBody
     */
    protected $body = null;

    /**
     * @var EmailAttachment[]
     */
    protected $attachments;

    /**
     * Constructor
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get item id
     *
     * @return ItemId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set item id
     *
     * @param ItemId $id
     * @return $this
     */
    public function setId(ItemId $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get email subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set email subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get FROM email
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set FROM email
     *
     * @param string $email
     * @return $this
     */
    public function setFrom($email)
    {
        $this->from = $email;

        return $this;
    }

    /**
     * Get email TO recipients
     *
     * @return string[]
     */
    public function getToRecipients()
    {
        return $this->toRecipients;
    }

    /**
     * Add email TO recipient
     *
     * @param string $email
     * @return $this
     */
    public function addToRecipient($email)
    {
        $this->toRecipients[] = $email;

        return $this;
    }

    /**
     * Get email CC recipients
     *
     * @return string[]
     */
    public function getCcRecipients()
    {
        return $this->ccRecipients;
    }

    /**
     * Add email CC recipient
     *
     * @param string $email
     * @return $this
     */
    public function addCcRecipient($email)
    {
        $this->ccRecipients[] = $email;

        return $this;
    }

    /**
     * Get email BCC recipients
     *
     * @return string[]
     */
    public function getBccRecipients()
    {
        return $this->bccRecipients;
    }

    /**
     * Add email BCC recipient
     *
     * @param string $email
     * @return $this
     */
    public function addBccRecipient($email)
    {
        $this->bccRecipients[] = $email;

        return $this;
    }

    /**
     * Get date/time when email received
     *
     * @return \DateTime
     */
    public function getReceivedAt()
    {
        return $this->receivedAt;
    }

    /**
     * Set date/time when email received
     *
     * @param \DateTime $receivedAt
     * @return $this
     */
    public function setReceivedAt($receivedAt)
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    /**
     * Get date/time when email sent
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set date/time when email sent
     *
     * @param \DateTime $sentAt
     * @return $this
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get email importance. -1 = LOW, 0 = NORMAL, 1 = HIGH
     *
     * @return integer
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Set email importance
     *
     * @param integer -1 = LOW, 0 = NORMAL, 1 = HIGH
     * @return $this
     */
    public function setImportance($importance)
    {
        $this->importance = $importance;

        return $this;
    }

    /**
     * Get email internal date receives from an email server
     *
     * @return \DateTime
     */
    public function getInternalDate()
    {
        return $this->internalDate;
    }

    /**
     * Set email internal date receives from an email server
     *
     * @param \DateTime $internalDate
     * @return $this
     */
    public function setInternalDate($internalDate)
    {
        $this->internalDate = $internalDate;

        return $this;
    }

    /**
     * Get value of email Message-ID header
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Set value of email Message-ID header
     *
     * @param string $messageId
     * @return $this
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * Get email message id uses for group related messages
     *
     * @return string
     */
    public function getXMessageId()
    {
        return $this->xMessageId;
    }

    /**
     * Set email message id uses for group related messages
     *
     * @param string $xMessageId
     * @return $this
     */
    public function setXMessageId($xMessageId)
    {
        $this->xMessageId = $xMessageId;

        return $this;
    }

    /**
     * Get email thread id uses for group related messages
     *
     * @return string
     */
    public function getXThreadId()
    {
        return $this->xThreadId;
    }

    /**
     * Set email thread id uses for group related messages
     *
     * @param $xThreadId
     * @return $this
     */
    public function setXThreadId($xThreadId)
    {
        $this->xThreadId = $xThreadId;

        return $this;
    }

    /**
     * Get email body
     *
     * @return EmailBody
     */
    public function getBody()
    {
        if ($this->body === null) {
            $this->body = new EmailBody();

            $body = $this->message->getBody();
            try {
                $this->body->setContent($body->getContent(Body::FORMAT_HTML)->getDecodedContent());
                $this->body->setBodyIsText(false);
            } catch (InvalidBodyFormatException $ex) {
                $this->body->setContent($body->getContent(Body::FORMAT_TEXT)->getDecodedContent());
                $this->body->setBodyIsText(true);
            }
        }

        return $this->body;
    }

    /**
     * Get email attachments
     *
     * @return EmailAttachment[]
     */
    public function getAttachments()
    {
        if ($this->attachments === null) {
            $this->attachments = array();

            foreach ($this->message->getAttachments() as $a) {
                $content = $a->getContent();
                $attachment = new EmailAttachment();
                $attachment
                    ->setFileName($a->getFileName()->getDecodedValue())
                    ->setContent($content->getContent())
                    ->setContentType($content->getContentType())
                    ->setContentTransferEncoding($content->getContentTransferEncoding());
                $this->attachments[] = $attachment;
            }
        }

        return $this->attachments;
    }
}
