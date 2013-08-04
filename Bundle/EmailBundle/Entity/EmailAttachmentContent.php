<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * Email Attachment
 *
 * @ORM\Table(name="oro_email_attachment_content")
 * @ORM\Entity
 */
class EmailAttachmentContent
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Exclude
     */
    protected $id;

    /**
     * @var EmailAttachment
     *
     * @ORM\OneToOne(targetEntity="EmailAttachment", inversedBy="attachmentContent")
     * @ORM\JoinColumn(name="attachment_id", referencedColumnName="id", nullable=false)
     * @Exclude
     */
    protected $emailAttachment;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $content;

    /**
     * @var string
     *
     * @ORM\Column(name="content_transfer_encoding", type="string", length=20, nullable=false)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $contentTransferEncoding;

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
     * Get email attachment owner
     *
     * @return EmailAttachment
     */
    public function getEmailAttachment()
    {
        return $this->emailAttachment;
    }

    /**
     * Set email attachment owner
     *
     * @param EmailAttachment $emailAttachment
     * @return $this
     */
    public function setEmailAttachment(EmailAttachment $emailAttachment)
    {
        $this->emailAttachment = $emailAttachment;

        return $this;
    }

    /**
     * Get attachment content
     *
     * @return string
     */
    public function getValue()
    {
        return $this->content;
    }

    /**
     * Set attachment content
     *
     * @param string $content
     * @return $this
     */
    public function setValue($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get encoding type of attachment content
     *
     * @return string
     */
    public function getContentTransferEncoding()
    {
        return $this->contentTransferEncoding;
    }

    /**
     * Set encoding type of attachment content
     *
     * @param string $contentTransferEncoding
     * @return $this
     */
    public function setContentTransferEncoding($contentTransferEncoding)
    {
        $this->contentTransferEncoding = $contentTransferEncoding;

        return $this;
    }
}
