<?php

namespace Oro\Bundle\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_cron_schedule", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="UQ_COMMAND", columns={"command"})
 * })
 * @ORM\Entity
 */
class Schedule
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="command", type="string", length=50)
     */
    protected $command;

    /**
     * @var string
     *
     * @ORM\Column(name="definition", type="string", length=100, nullable=true)
     */
    protected $definition;

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
     * Get command name
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set command name
     *
     * @param  string  $command
     * @return Shedule
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Returns cron definition string
     *
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set cron definition string
     *
     * General format:
     * *    *    *    *    *
     * ┬    ┬    ┬    ┬    ┬
     * │    │    │    │    │
     * │    │    │    │    │
     * │    │    │    │    └───── day of week (0 - 6) (0 to 6 are Sunday to Saturday, or use names)
     * │    │    │    └────────── month (1 - 12)
     * │    │    └─────────────── day of month (1 - 31)
     * │    └──────────────────── hour (0 - 23)
     * └───────────────────────── min (0 - 59)
     *
     * Predefined values are:
     *  @yearly (or @annually)  Run once a year at midnight in the morning of January 1                 0 0 1 1 *
     *  @monthly                Run once a month at midnight in the morning of the first of the month   0 0 1 * *
     *  @weekly                 Run once a week at midnight in the morning of Sunday                    0 0 * * 0
     *  @daily                  Run once a day at midnight                                              0 0 * * *
     *  @hourly                 Run once an hour at the beginning of the hour                           0 * * * *
     *
     * @param  string  $definition New cron definition
     * @return Shedule
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }
}
