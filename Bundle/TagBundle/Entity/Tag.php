<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Tag
 *
 * @ORM\Table(name="oro_tag_tag")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Oro\Bundle\TagBundle\Entity\Repository\TagRepository")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Tag", "plural_label"="Tags"},
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="user_owner_id"
 *      }
 *  }
 * )
 */
class Tag implements ContainAuthorInterface, ContainUpdaterInterface
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
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     */
    protected $name;

    /**
     * @var \Datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Datetime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\OneToMany(targetEntity="Tagging", mappedBy="tag", fetch="LAZY")
     */
    protected $tagging;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $updatedBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * Constructor
     *
     * @param string $name Tag's name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
        $this->tagging = new ArrayCollection();

        $this->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
    }

    /**
     * Returns tag's id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the tag's name
     *
     * @param string $name Name to set
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns tag's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set created date
     *
     * @param \DateTime $date
     * @return $this
     */
    public function setCreatedAt(\DateTime $date)
    {
        $this->created = $date;

        return $this;
    }

    /**
     * Get created date
     *
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Set updated date
     *
     * @param \DateTime $date
     * @return $this
     */
    public function setUpdatedAt(\DateTime $date)
    {
        $this->updated = $date;

        return $this;
    }

    /**
     * Get updated date
     *
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Return tagging object
     *
     * @return PersistentCollection
     */
    public function getTagging()
    {
        return $this->tagging;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedBy(User $user)
    {
        $this->updatedBy = $user;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedBy(User $user)
    {
        $this->createdBy = $user;

        return $this;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Pre update event handler
     * @ORM\PreUpdate
     */
    public function doUpdate()
    {
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owningUser
     * @return Tag
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }
}
