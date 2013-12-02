<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * Email Body
 *
 * @ORM\Table(name="oro_email_body")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class EmailBody
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Type("integer")
     */
    protected $id;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created", type="datetime")
     * @Type("dateTime")
     */
    protected $created;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Soap\ComplexType("string", name="content")
     * @Type("string")
     */
    protected $bodyContent;

    /**
     * @var bool
     *
     * @ORM\Column(name="body_is_text", type="boolean")
     * @Soap\ComplexType("boolean")
     * @Type("boolean")
     */
    protected $bodyIsText;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_attachments", type="boolean")
     * @Type("boolean")
     */
    protected $hasAttachments;

    /**
     * @var bool
     *
     * @ORM\Column(name="persistent", type="boolean")
     * @Type("boolean")
     */
    protected $persistent;

    /**
     * @var Email
     *
     * @ORM\ManyToOne(targetEntity="Email", inversedBy="emailBody")
     * @ORM\JoinColumn(name="email_id", referencedColumnName="id")
     * @Exclude
     */
    protected $header;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EmailAttachment", mappedBy="emailBody",
     *      cascade={"persist", "remove"}, orphanRemoval=true)
     * @Soap\ComplexType("Oro\Bundle\EmailBundle\Entity\EmailAttachment[]")
     * @Exclude
     */
    protected $attachments;

    public function __construct()
    {
        $this->bodyIsText = false;
        $this->hasAttachments = false;
        $this->persistent = false;
        $this->attachments = new ArrayCollection();
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
     * Get body content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->bodyContent;
    }

    /**
     * Set body content.
     *
     * @param string $bodyContent
     * @return $this
     */
    public function setContent($bodyContent)
    {
        $this->bodyContent = ($bodyContent === null ? '' : $bodyContent);

        return $this;
    }

    /**
     * Indicate whether email body is a text or html.
     *
     * @return bool true if body is text/plain; otherwise, the body content is text/html
     */
    public function getBodyIsText()
    {
        return $this->bodyIsText;
    }

    /**
     * Set body content type.
     *
     * @param bool $bodyIsText true for text/plain, false for text/html
     * @return $this
     */
    public function setBodyIsText($bodyIsText)
    {
        $this->bodyIsText = $bodyIsText;

        return $this;
    }

    /**
     * Indicate whether email has attachments or not.
     *
     * @return bool true if body is text/plain; otherwise, the body content is text/html
     */
    public function getHasAttachments()
    {
        return $this->hasAttachments;
    }

    /**
     * Set flag indicates whether there are attachments or not.
     *
     * @param bool $hasAttachments
     * @return $this
     */
    public function setHasAttachments($hasAttachments)
    {
        $this->hasAttachments = $hasAttachments;

        return $this;
    }

    /**
     * Indicate whether email is persistent or not.
     *
     * @return bool true if this email newer removed from the cache; otherwise, false
     */
    public function getPersistent()
    {
        return $this->persistent;
    }

    /**
     * Set flag indicates whether email can be removed from the cache or not.
     *
     * @param bool $persistent true if this email newer removed from the cache; otherwise, false
     * @return $this
     */
    public function setPersistent($persistent)
    {
        $this->persistent = $persistent;

        return $this;
    }

    /**
     * Get email header
     *
     * @return Email
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set email header
     *
     * @param Email $header
     * @return $this
     */
    public function setHeader(Email $header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get email attachments
     *
     * @return EmailAttachment[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Add email attachment
     *
     * @param  EmailAttachment $attachment
     * @return $this
     */
    public function addAttachment(EmailAttachment $attachment)
    {
        $this->setHasAttachments(true);

        $this->attachments[] = $attachment;

        $attachment->setEmailBody($this);

        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = EmailUtil::currentUTCDateTime();
    }
}
