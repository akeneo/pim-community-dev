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
 *          "permissions"="VIEW;CREATE;EDIT;DELETE",
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
     * ArrayCollection|Calendar[]
     *
     * @ORM\ManyToMany(targetEntity="Calendar", cascade={"persist"})
     * @ORM\JoinTable(name="oro_calendar_link",
     *      joinColumns={@ORM\JoinColumn(name="calendar_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(
     *          name="attached_calendar_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $attachedCalendars;

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
        $this->attachedCalendars = new ArrayCollection();
        $this->events = new ArrayCollection();
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
     * @param  string $name
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
     * Gets the calendars attached to this one
     *
     * @return Calendar[]
     */
    public function getAttachedCalendars()
    {
        return $this->attachedCalendars;
    }

    /**
     * Connects another calendar to this one
     *
     * @param Calendar $calendar
     * @return Calendar
     * @throws InvalidEntityException
     */
    public function attachCalendar(Calendar $calendar)
    {
        if ($this === $calendar) {
            throw new InvalidEntityException("The calendar cannot be attached to itself.");
        }

        if (!$this->attachedCalendars->contains($calendar)) {
            $this->attachedCalendars->add($calendar);
        }

        return $this;
    }

    /**
     * Disconnects another calendar from this one
     *
     * @param Calendar $calendar
     * @return Calendar
     */
    public function detachCalendar(Calendar $calendar)
    {
        if ($this->attachedCalendars->contains($calendar)) {
            $this->attachedCalendars->removeElement($calendar);
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
