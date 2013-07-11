<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Table(
 *     name="oro_tag_tagging",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="tagging_idx", columns={"tag_id", "entity_name", "record_id", "created_by"})}
 * )
 * @ORM\Entity
 */
class Tagging implements ContainAuthorInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tag", inversedBy="tagging")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $tag;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @var \Datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var string
     * @ORM\Column(name="alias", type="string", length=100)
     */
    protected $alias;

    /**
     * @var string
     * @ORM\Column(name="entity_name", type="string", length=100)
     */
    protected $entityName;

    /**
     * @var int
     *@ORM\Column(name="record_id", type="integer")
     */
    protected $recordId;

    /**
     * Constructor
     */
    public function __construct(Tag $tag = null, Taggable $resource = null)
    {
        if ($tag != null) {
            $this->setTag($tag);
        }

        if ($resource != null) {
            $this->setResource($resource);
        }

        $this->setCreatedAt(new \DateTime('now'));
    }

    /**
     * Returns tagging id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the tag object
     *
     * @param Tag $tag Tag to set
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * Returns the tag object
     *
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Sets the resource
     *
     * @param Taggable $resource Resource to set
     */
    public function setResource(Taggable $resource)
    {
        $this->entityName = get_class($resource);
        $this->recordId = $resource->getTaggableId();
    }

    /**
     * Returns the tagged resource type
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Setter for alias
     *
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Getter for alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Returns the tagged resource id
     *
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
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
}
