<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * Email Attachment
 *
 * @ORM\Table(name="oro_email_attachment")
 * @ORM\Entity
 */
class EmailAttachment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int")
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=100)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $contentType;

    /**
     * @var EmailAttachmentContent
     *
     * @ORM\OneToOne(targetEntity="EmailAttachmentContent", mappedBy="emailAttachment", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $attachmentContent;

    /**
     * @var EmailBody
     *
     * @ORM\ManyToOne(targetEntity="EmailBody", inversedBy="attachments")
     * @ORM\JoinColumn(name="body_id", referencedColumnName="id")
     * @Exclude
     */
    protected $emailBody;

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
     * Get attachment file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set attachment file name
     *
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get content type. It may be any MIME type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set content type
     *
     * @param string $contentType any MIME type
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get content of email attachment
     *
     * @return EmailAttachmentContent
     */
    public function getContent()
    {
        return $this->attachmentContent;
    }

    /**
     * Set content of email attachment
     *
     * @param  EmailAttachmentContent $attachmentContent
     * @return $this
     */
    public function setContent(EmailAttachmentContent $attachmentContent)
    {
        $this->attachmentContent = $attachmentContent;

        $attachmentContent->setEmailAttachment($this);

        return $this;
    }

    /**
     * Get email body
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
     * @param EmailBody $emailBody
     * @return $this
     */
    public function setEmailBody(EmailBody $emailBody)
    {
        $this->emailBody = $emailBody;

        return $this;
    }
}
