<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

/**
 * Email Origin
 *
 * @ORM\Table(name="oro_email_origin")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="name", type="string", length=30)
 */
abstract class EmailOrigin
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EmailFolder", mappedBy="origin", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $folders;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->folders = new ArrayCollection();
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
     * Get email folders
     *
     * @return EmailFolder[]
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * Add folder
     *
     * @param  EmailFolder $folder
     * @return $this
     */
    public function addFolder(EmailFolder $folder)
    {
        $this->folders[] = $folder;

        $folder->setOrigin($this);

        return $this;
    }

    /**
     * Get a human-readable representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('EmailOrigin(%d)', $this->id);
    }
}
