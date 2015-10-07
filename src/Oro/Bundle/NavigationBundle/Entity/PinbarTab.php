<?php

namespace Oro\Bundle\NavigationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pinbar Tab Entity
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\NavigationBundle\Entity\Repository\PinbarTabRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="oro_navigation_item_pinbar")
 */
class PinbarTab implements NavigationItemInterface
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
     * @var NavigationItem $item
     *
     * @ORM\OneToOne(targetEntity="NavigationItem", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $item;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="maximized", type="datetime", nullable=true)
     */
    protected $maximized;

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
     * Get maximizeDate
     *
     * @return \DateTime
     */
    public function getMaximized()
    {
        return $this->maximized;
    }

    /**
     * Set maximizeDate
     *
     * @param  boolean   $maximizeDate
     * @return PinbarTab
     */
    public function setMaximized($maximizeDate)
    {
        $this->maximized = $maximizeDate ? new \DateTime() : null;

        return $this;
    }

    /**
     * Set user
     *
     * @param  NavigationItem                                $item
     * @return \Oro\Bundle\NavigationBundle\Entity\PinbarTab
     */
    public function setItem(NavigationItem $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get user
     *
     * @return NavigationItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function doPrePersist()
    {
        $this->maximized = null;
    }

    /**
     * Get user
     *
     * @return \Pim\Bundle\UserBundle\Entity\UserInterface
     */
    public function getUser()
    {
        if ($this->getItem()) {
            return $this->getItem()->getUser();
        }

        return null;
    }

    /**
     * Set entity properties
     *
     * @param array $values
     */
    public function setValues(array $values)
    {
        if (isset($values['maximized'])) {
            $this->setMaximized((bool) $values['maximized']);
        }
        if ($this->getItem()) {
            $this->getItem()->setValues($values);
        }
    }
}
