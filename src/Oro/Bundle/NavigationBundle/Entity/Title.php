<?php

namespace Oro\Bundle\NavigationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Title
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\NavigationBundle\Entity\Repository\TitleRepository")
 * @ORM\Table(name="oro_navigation_title", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unq_route", columns={"route"})
 * })
 */
class Title
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255)
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="short_title", type="string", length=255)
     */
    private $shortTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="is_system", type="boolean")
     */
    private $isSystem = true;

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
     * Set route
     *
     * @param  string $route
     * @return Title
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set title
     *
     * @param  string $title
     * @return Title
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
     * Set short title
     *
     * @param  string $shortTitle
     * @return Title
     */
    public function setShortTitle($shortTitle)
    {
        $this->shortTitle = $shortTitle;

        return $this;
    }

    /**
     * Get short title
     *
     * @return string
     */
    public function getShortTitle()
    {
        return $this->shortTitle;
    }

    /**
     * Set is system
     *
     * @param  bool  $value
     * @return Title
     */
    public function setIsSystem($value)
    {
        $this->isSystem = $value;

        return $this;
    }

    /**
     * Returns is title not changed in db
     *
     * @return bool
     */
    public function getIsSystem()
    {
        return $this->isSystem;
    }
}
