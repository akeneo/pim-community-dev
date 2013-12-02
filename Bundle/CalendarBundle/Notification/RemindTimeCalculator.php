<?php

namespace Oro\Bundle\CalendarBundle\Notification;

class RemindTimeCalculator
{
    /**
     * @var int
     */
    protected $reminderTime;

    /**
     * Constructor
     *
     * @param int $reminderTime A time before a calendar event occurs to get a reminder message
     */
    public function __construct($reminderTime)
    {
        $this->reminderTime = $reminderTime;
    }

    /**
     * Returns date/time when an remind notification should occurs.
     *
     * @param \DateTime $start A date/time an event begins.
     * @return \DateTime
     */
    public function calculateRemindAt(\DateTime $start)
    {
        $result = clone $start;
        $result->sub(new \DateInterval(sprintf('PT%sM', $this->reminderTime)));

        return $result;
    }
}
