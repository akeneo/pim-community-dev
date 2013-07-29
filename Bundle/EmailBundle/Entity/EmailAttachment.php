<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255)
     */
    protected $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=100)
     */
    protected $contentType;

    /**
     * @var EmailBody
     *
     * @ORM\ManyToOne(targetEntity="EmailBody", inversedBy="folders")
     * @ORM\JoinColumn(name="body_id", referencedColumnName="id")
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
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
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
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
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
