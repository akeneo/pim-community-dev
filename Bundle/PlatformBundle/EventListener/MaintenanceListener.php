<?php

namespace Oro\Bundle\PlatformBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;

use Oro\Bundle\PlatformBundle\Command\MaintenanceCommandInterface;
use Oro\Bundle\PlatformBundle\Maintenance\Mode;

class MaintenanceListener
{
    /**
     * @var Mode
     */
    protected $mode;

    /**
     * @param Mode $mode
     */
    public function __construct(Mode $mode)
    {
        $this->mode = $mode;
    }

    /**
     * Check if application is in maintenance mode, and, if so - disable command execution
     *
     * @param ConsoleCommandEvent $event
     */
    public function onCommandExecute(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        if ($this->mode->isOn()
            && !($command instanceof MaintenanceCommandInterface)
            && $command->getName() != 'lexik:maintenance:unlock'
        ) {
            throw new \RuntimeException('Unable to launch command in maintenance mode');
        }
    }
}