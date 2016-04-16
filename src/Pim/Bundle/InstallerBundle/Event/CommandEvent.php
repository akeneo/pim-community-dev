<?php
namespace Pim\Bundle\InstallerBundle\Event;

use Pim\Bundle\InstallerBundle\SimpleCommand\SimpleCommandInterface;

class CommandEvent extends InstallEvent
{
    /** @var SimpleCommandInterface */
    protected $command;

    public function __construct(SimpleCommandInterface $command, array $arguments = [])
    {
        $this->command = $command;
        parent::__construct($command, $arguments);
    }

    /**
     * Gets the command
     *
     * @return SimpleCommandInterface
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Sets the command
     *
     * @param SimpleCommandInterface $command
     *
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }
}