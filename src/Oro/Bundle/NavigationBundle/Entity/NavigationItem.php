<?php

namespace Oro\Bundle\NavigationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Navigation Entity
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\NavigationBundle\Entity\Repository\NavigationItemRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="oro_navigation_item",
 *      indexes={@ORM\Index(name="sorted_items_idx", columns={"user_id", "position"})})
 */
class NavigationItem implements NavigationItemInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Pim\Bundle\UserBundle\Entity\UserInterface $user
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\UserBundle\Entity\UserInterface")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=false)
     */
    protected $type;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=1023)
     */
    protected $url;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string")
     */
    protected $title;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="smallint")
     */
    protected $position;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct(array $values = null)
    {
        if (!empty($values)) {
            $this->setValues($values);
        }
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
     * Set type
     *
     * @param  string    $type
     * @return PinbarTab
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url
     *
     * @param  string    $url
     * @return PinbarTab
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set title
     *
     * @param  string    $title
     * @return PinbarTab
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set position
     *
     * @param  integer   $position
     * @return PinbarTab
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set createdAt
     *
     * @param  \DateTime $createdAt
     * @return PinbarTab
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param  \DateTime $updatedAt
     * @return PinbarTab
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set user
     *
     * @param  \Pim\Bundle\UserBundle\Entity\UserInterface $user
     * @return PinbarTab
     */
    public function setUser(\Pim\Bundle\UserBundle\Entity\UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Pim\Bundle\UserBundle\Entity\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set entity properties
     *
     * @param array $values
     */
    public function setValues(array $values)
    {
        if (isset($values['title'])) {
            $this->setTitle($values['title']);
        }
        if (isset($values['url'])) {
            $this->setUrl($values['url']);
        }
        if (isset($values['position'])) {
            $this->setPosition($values['position']);
        }
        if (isset($values['user'])) {
            $this->setUser($values['user']);
        }
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function doPrePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = $this->createdAt;
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function doPreUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
