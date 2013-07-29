<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Exclude;

/**
 * Email
 *
 * @ORM\Table(name="oro_email")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Email
{
    const LOW_IMPORTANCE = -1;
    const NORMAL_IMPORTANCE = 0;
    const HIGH_IMPORTANCE = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=500)
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="from_name", type="string", length=255)
     */
    protected $fromName;

    /**
     * @var EmailAddress
     *
     * @ORM\ManyToOne(targetEntity="EmailAddress", fetch="EAGER")
     * @ORM\JoinColumn(name="from_email_address_id", referencedColumnName="id", nullable=false)
     */
    protected $fromEmailAddress;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EmailRecipient", mappedBy="email", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $recipients;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="received", type="datetime")
     */
    protected $received;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent", type="datetime")
     */
    protected $sent;

    /**
     * @var integer
     *
     * @ORM\Column(name="importance", type="integer")
     */
    protected $importance;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="internaldate", type="datetime")
     */
    protected $internalDate;

    /**
     * @var string
     *
     * @ORM\Column(name="message_id", type="string", length=255, nullable=true)
     */
    protected $messageId;

    /**
     * @var string
     *
     * @ORM\Column(name="x_message_id", type="string", length=255, nullable=true)
     */
    protected $xMessageId;

    /**
     * @var string
     *
     * @ORM\Column(name="x_thread_id", type="string", length=255, nullable=true)
     */
    protected $xThreadId;

    /**
     * @var EmailFolder
     *
     * @ORM\ManyToOne(targetEntity="EmailFolder", inversedBy="emails")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id")
     */
    protected $folder;

    /**
     * @var EmailBody
     *
     * @ORM\OneToOne(targetEntity="EmailBody", mappedBy="header", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $emailBody;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get entity created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
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
     * Get FROM email name
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set FROM email name
     *
     * @param string $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Get FROM email address
     *
     * @return EmailAddress
     */
    public function getFromEmailAddress()
    {
        return $this->fromEmailAddress;
    }

    /**
     * Set FROM email address
     *
     * @param EmailAddress $fromEmailAddress
     * @return $this
     */
    public function setFromEmailAddress(EmailAddress $fromEmailAddress)
    {
        $this->fromEmailAddress = $fromEmailAddress;

        return $this;
    }

    /**
     * Get email recipients
     *
     * @param null|string $recipientType null to get all recipients, or 'to', 'cc' or 'bcc' if you need specific type of recipients
     * @return EmailRecipient[]
     */
    public function getRecipients($recipientType = null)
    {
        if ($recipientType === null) {
            return $this->recipients;
        }

        return $this->recipients->filter(
            function ($recipient) use ($recipientType) {
                /** @var EmailRecipient $recipient */
                return $recipient->getType() === $recipientType;
            }
        );
    }

    /**
     * Add folder
     *
     * @param  EmailRecipient $recipient
     * @return $this
     */
    public function addRecipient(EmailRecipient $recipient)
    {
        $this->recipients[] = $recipient;

        $recipient->setEmail($this);

        return $this;
    }

    /**
     * Get date/time when email received
     *
     * @return \DateTime
     */
    public function getReceivedAt()
    {
        return $this->received;
    }

    /**
     * Set date/time when email received
     *
     * @param \DateTime $received
     * @return $this
     */
    public function setReceivedAt($received)
    {
        $this->received = $received;

        return $this;
    }

    /**
     * Get date/time when email sent
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sent;
    }

    /**
     * Set date/time when email sent
     *
     * @param \DateTime $sent
     * @return $this
     */
    public function setSentAt($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get email importance
     *
     * @return integer Can be one of *_IMPORTANCE constants
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Set email importance
     *
     * @param integer $importance Can be one of *_IMPORTANCE constants
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
     * @param string $xThreadId
     * @return $this
     */
    public function setXThreadId($xThreadId)
    {
        $this->xThreadId = $xThreadId;

        return $this;
    }

    /**
     * Get email folder
     *
     * @return EmailFolder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set email folder
     *
     * @param  EmailFolder $folder
     * @return $this
     */
    public function setFolder(EmailFolder $folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get cached email body
     *
     * @return EmailBody
     */
    public function getEmailBody()
    {
        return $this->emailBody;
    }

    /**
     * Set email body
     *
     * @param  EmailBody $emailBody
     * @return $this
     */
    public function setEmailBody(EmailBody $emailBody)
    {
        $this->emailBody = $emailBody;

        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        if (!isset($this->importance)) {
            $this->importance = self::NORMAL_IMPORTANCE;
        }
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
