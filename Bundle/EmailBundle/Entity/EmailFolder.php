<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * Email Folder
 *
 * @ORM\Table(name="oro_email_folder")
 * @ORM\Entity
 */
class EmailFolder
{
    const INBOX = 'inbox';
    const SENT = 'sent';
    const TRASH = 'trash';
    const DRAFTS = 'drafts';
    const OTHER = 'other';

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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $type;

    /**
     * @var EmailOrigin
     *
     * @ORM\ManyToOne(targetEntity="EmailOrigin", inversedBy="folders")
     * @ORM\JoinColumn(name="origin_id", referencedColumnName="id")
     * @Exclude
     */
    protected $origin;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="folder", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $emails;

    public function __construct()
    {
        $this->emails = new ArrayCollection();
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
     * Get folder name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set folder name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get folder type.
     *
     * @return string Can be 'inbox', 'sent', 'trash', 'drafts' or 'other'
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set folder type
     *
     * @param string $type Can be 'inbox', 'sent', 'trash', 'drafts' or 'other'
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get email folder origin
     *
     * @return EmailOrigin
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set email folder origin
     *
     * @param EmailOrigin $origin
     * @return $this
     */
    public function setOrigin(EmailOrigin $origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get emails
     *
     * @return Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add email
     *
     * @param  Email $email
     * @return $this
     */
    public function addEmail(Email $email)
    {
        $this->emails[] = $email;

        $email->setFolder($this);

        return $this;
    }
}
