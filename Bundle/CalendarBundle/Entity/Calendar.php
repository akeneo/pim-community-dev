<?php

namespace Oro\Bundle\CalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository")
 * @ORM\Table(name="oro_calendar")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Calendar", "plural_label"="Calendars"},
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="user_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "permissions"="VIEW",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Calendar
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var ArrayCollection|CalendarConnection[]
     *
     * @ORM\OneToMany(targetEntity="CalendarConnection", mappedBy="calendar", cascade={"persist"}, orphanRemoval=true)
     */
    protected $connections;

    /**
     * @var ArrayCollection|CalendarEvent[]
     *
     * @ORM\OneToMany(targetEntity="CalendarEvent", mappedBy="calendar", cascade={"persist"})
     */
    protected $events;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connections = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return empty($this->name) ? '[default]' : $this->name;
    }

    /**
     * Gets the calendar id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets calendar name.
     * Usually user's default calendar has no name and this method returns null.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets calendar name.
     *
     * @param  string|null $name
     * @return Calendar
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets owning user for this calendar
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Sets owning user for this calendar
     *
     * @param User $owningUser
     * @return Calendar
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * Gets connections represent calendars connected to this calendar
     *
     * @return CalendarConnection[]
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Connects another calendar to this calendar
     *
     * @param CalendarConnection $connection
     * @return Calendar
     * @throws InvalidEntityException
     */
    public function addConnection(CalendarConnection $connection)
    {
        if ($connection->getCalendar() !== null) {
            throw new InvalidEntityException("The already connected calendar cannot be re-connected.");
        }
        if ($connection->getConnectedCalendar() === null) {
            throw new InvalidEntityException("The connected calendar must be specified.");
        }

        if (!$this->connections->contains($connection)) {
            $connection->setCalendar($this);
            $this->connections->add($connection);
        }

        return $this;
    }

    /**
     * Detaches another calendar from this calendar
     *
     * @param CalendarConnection $connection
     * @return Calendar
     */
    public function removeConnection(CalendarConnection $connection)
    {
        if ($this->connections->contains($connection)) {
            $this->connections->removeElement($connection);
        }

        return $this;
    }

    /**
     * Gets all events of this calendar.
     *
     * @return CalendarEvent[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Adds an event to this calendar.
     *
     * @param  CalendarEvent $event
     * @return Calendar
     */
    public function addEvent(CalendarEvent $event)
    {
        $this->events[] = $event;

        $event->setCalendar($this);

        return $this;
    }
}
