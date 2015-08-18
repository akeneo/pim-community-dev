<?php

namespace Oro\Bundle\NavigationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * Page state entity
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="oro_navigation_pagestate")
 */
class PageState
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
     * @var UserInterface $user
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\UserBundle\Entity\UserInterface")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * Base64 encoded page URL
     *
     * @var string $pageId
     *
     * @ORM\Column(name="page_id", type="string", length=4000)
     */
    protected $pageId;

    /**
     * Hash of page id, used for quick access/search
     *
     * @var string $pageHash
     *
     * @ORM\Column(name="page_hash", type="string", length=32, unique=true)
     */
    protected $pageHash;

    /**
     * @var string $data
     *
     * @ORM\Column(name="data", type="text", nullable=false)
     */
    protected $data;

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

    public function __construct()
    {
        $this->pageHash = self::generateHash($this->pageId);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param  UserInterface $user
     * @return PageState
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set page id
     *
     * @param  string    $pageId
     * @return PageState
     */
    public function setPageId($pageId)
    {
        $this->pageId   = $pageId;
        $this->pageHash = self::generateHash($pageId);

        return $this;
    }

    /**
     * Get page id
     *
     * @return string
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Get page hash
     *
     * @return string
     */
    public function getPageHash()
    {
        return $this->pageHash;
    }

    /**
     * Generate unique hash for page id
     *
     * @param  string $pageId
     * @return string
     */
    public static function generateHash($pageId)
    {
        return md5($pageId);
    }

    /**
     * Set data
     *
     * @param  string    $data
     * @return PageState
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set createdAt
     *
     * @param  \DateTime $createdAt
     * @return PageState
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
     * @return PageState
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
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function doPrePersist()
    {
        $this->createdAt =
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
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
