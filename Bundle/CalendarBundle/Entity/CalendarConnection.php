<?php

namespace Oro\Bundle\CalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\CalendarBundle\Entity\Repository\CalendarConnectionRepository")
 * @ORM\Table(name="oro_calendar_connection",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="oro_calendar_connection_uq",
 *                              columns={"calendar_id", "connected_calendar_id"})})
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Calendar of other users", "plural_label"="Calendars of other users"},
 *      "security"={
 *          "type"="ACL",
 *          "permissions"="VIEW",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class CalendarConnection
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $createdAt;

    /**
     * @var Calendar
     *
     * @ORM\ManyToOne(targetEntity="Calendar", inversedBy="connections")
     * @ORM\JoinColumn(name="calendar_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $calendar;

    /**
     * @var Calendar
     *
     * @ORM\ManyToOne(targetEntity="Calendar")
     * @ORM\JoinColumn(name="connected_calendar_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $connectedCalendar;

    /**
     * @var string|null
     *
     * @ORM\Column(name="color", type="string", length=6, nullable=true)
     */
    protected $color;

    /**
     * @var string|null
     *
     * @ORM\Column(name="background_color", type="string", length=6, nullable=true)
     */
    protected $backgroundColor;

    /**
     * Constructor
     *
     * @param Calendar $connectedCalendar
     */
    public function __construct(Calendar $connectedCalendar)
    {
        $this->connectedCalendar = $connectedCalendar;
    }

    /**
     * Gets the connection id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get entity created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Gets calendar object.
     *
     * @return Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Sets calendar object.
     *
     * @param Calendar $calendar
     * @return CalendarConnection
     */
    public function setCalendar(Calendar $calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Gets connected calendar object.
     *
     * @return Calendar
     */
    public function getConnectedCalendar()
    {
        return $this->connectedCalendar;
    }

    /**
     * Sets connected calendar object.
     *
     * @param Calendar $connectedCalendar
     * @return CalendarConnection
     */
    public function setConnectedCalendar(Calendar $connectedCalendar)
    {
        $this->connectedCalendar = $connectedCalendar;

        return $this;
    }

    /**
     * Gets a text color of the connected calendar events.
     * If this method returns null the text color should be calculated automatically on UI.
     *
     * @return string|null The color in hex format, for example F00 or FF0000 for a red color.
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Sets a text color of the connected calendar events.
     *
     * @param string|null $color The color in hex format, for example F00 or FF0000 for a red color.
     *                           Set it to null to allow UI to calculate the text color automatically.
     * @return CalendarConnection
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Gets a background color of the connected calendar events.
     * If this method returns null the background color should be calculated automatically on UI.
     *
     * @return string|null The color in hex format, for example F00 or FF0000 for a red color.
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Sets a background color of the connected calendar events.
     *
     * @param string|null $backgroundColor The color in hex format, for example F00 or FF0000 for a red color.
     *                                     Set it to null to allow UI to calculate the background color automatically.
     * @return CalendarConnection
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
