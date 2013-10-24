<?php

namespace Oro\Bundle\CalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\CalendarBundle\Entity\Repository\CalendarEventRepository")
 * @ORM\Table(name="oro_calendar_event",
 *      indexes={@ORM\Index(name="oro_calendar_event_idx", columns={"calendar_id", "start", "end"})})
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Calendar Event", "plural_label"="Calendar Events"},
 *      "security"={
 *          "type"="ACL",
 *          "permissions"="VIEW;CREATE;EDIT;DELETE",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class CalendarEvent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Calendar
     *
     * @ORM\ManyToOne(targetEntity="Calendar", inversedBy="events")
     * @ORM\JoinColumn(name="calendar_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $calendar;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=false)
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $end;

    /**
     * @var bool
     *
     * @ORM\Column(name="all_day", type="boolean")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $allDay;

    /**
     * @var bool
     *
     * @ORM\Column(name="reminder", type="boolean")
     */
    protected $reminder;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="remind_at", type="datetime", nullable=true)
     */
    protected $remindAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="reminded", type="boolean", options={"default":false})
     */
    protected $reminded;

    /**
     * Gets an calendar event id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets owning calendar
     *
     * @return Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Sets owning calendar
     *
     * @param Calendar $calendar
     * @return CalendarEvent
     */
    public function setCalendar(Calendar $calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Gets calendar name.
     * Usually user's default calendar has no name and this method returns null for it.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets calendar event title.
     *
     * @param  string $title
     * @return CalendarEvent
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets date/time an event begins.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Sets date/time an event begins.
     *
     * @param \DateTime $start
     * @return CalendarEvent
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Gets date/time an event ends.
     *
     * If an event is all-day the end date is inclusive.
     * This means an event with start Nov 10 and end Nov 12 will span 3 days on the calendar.
     *
     * If an event is NOT all-day the end date is exclusive.
     * This is only a gotcha when your end has time 00:00. It means your event ends on midnight,
     * and it will not span through the next day.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Sets date/time an event ends.
     *
     * @param \DateTime $end
     * @return CalendarEvent
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Indicates whether an event occurs at a specific time-of-day.
     *
     * @return bool
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * Sets a flag indicates whether an event occurs at a specific time-of-day.
     *
     * @param bool $allDay
     * @return CalendarEvent
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;

        return $this;
    }

    /**
     * Indicates whether an remind notification need to be send for an event.
     *
     * @return bool
     */
    public function getReminder()
    {
        return $this->reminder;
    }

    /**
     * Sets a flag indicates whether an remind notification need to be send for an event.
     *
     * @param bool $reminder
     * @return CalendarEvent
     */
    public function setReminder($reminder)
    {
        $this->reminder = $reminder;

        return $this;
    }

    /**
     * Gets date/time an remind notification should occurs.
     *
     * @return \DateTime
     */
    public function getRemindAt()
    {
        return $this->remindAt;
    }

    /**
     * Sets date/time an remind notification should occurs.
     *
     * @param \DateTime $remindAt
     * @return CalendarEvent
     */
    public function setRemindAt($remindAt)
    {
        $this->remindAt = $remindAt;

        return $this;
    }

    /**
     * Gets a flag indicates whether an remind notification has already occurred or not.
     *
     * @return bool
     */
    public function getReminded()
    {
        return $this->reminded;
    }

    /**
     * Sets a flag indicates whether an remind notification has already occurred or not.
     *
     * @param bool $reminded
     * @return CalendarEvent
     */
    public function setReminded($reminded)
    {
        $this->reminded = $reminded;

        return $this;
    }
}
