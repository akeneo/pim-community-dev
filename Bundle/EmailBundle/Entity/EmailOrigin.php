<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Email Origin
 *
 * @ORM\Table(name="oro_email_origin")
 * @ORM\Entity
 */
class EmailOrigin
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
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EmailFolder", mappedBy="origin", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $folders;

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
     * Get email origin name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email origin name
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
}
