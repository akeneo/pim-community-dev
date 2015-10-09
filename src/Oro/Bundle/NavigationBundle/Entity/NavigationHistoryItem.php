<?php

namespace Oro\Bundle\NavigationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Navigation History Entity
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\NavigationBundle\Entity\Repository\HistoryItemRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="oro_navigation_history")
 */
class NavigationHistoryItem implements NavigationItemInterface
{
    const NAVIGATION_HISTORY_ITEM_TYPE = 'history';

    const NAVIGATION_HISTORY_COLUMN_VISITED_AT = 'visitedAt';
    const NAVIGATION_HISTORY_COLUMN_VISIT_COUNT = 'visitCount';

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
     * @var \DateTime
     *
     * @ORM\Column(name="visited_at", type="datetime")
     */
    protected $visitedAt;

    /**
     * @var \int
     *
     * @ORM\Column(name="visit_count", type="integer")
     */
    protected $visitCount = 0;

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
     * Set url
     *
     * @param  string                $url
     * @return NavigationHistoryItem
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
     * @param  string                $title
     * @return NavigationHistoryItem
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
     * Set visitedAt
     *
     * @param  \DateTime             $visitedAt
     * @return NavigationHistoryItem
     */
    public function setVisitedAt($visitedAt)
    {
        $this->visitedAt = $visitedAt;

        return $this;
    }

    /**
     * Get visitedAt
     *
     * @return \DateTime
     */
    public function getVisitedAt()
    {
        return $this->visitedAt;
    }

    /**
     * Set visitCount
     *
     * @param  int                   $visitCount
     * @return NavigationHistoryItem
     */
    public function setVisitCount($visitCount)
    {
        $this->visitCount = $visitCount;

        return $this;
    }

    /**
     * Get visitCount
     *
     * @return int
     */
    public function getVisitCount()
    {
        return $this->visitCount;
    }

    /**
     * Set user
     *
     * @param  \Pim\Bundle\UserBundle\Entity\UserInterface $user
     * @return NavigationHistoryItem
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
        $this->visitedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     */
    public function doUpdate()
    {
        $this->visitedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->visitCount++;
    }
}
