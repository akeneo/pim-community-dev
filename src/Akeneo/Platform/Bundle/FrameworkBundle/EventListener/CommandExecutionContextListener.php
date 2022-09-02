<?php

namespace Akeneo\Platform\Bundle\FrameworkBundle\EventListener;

use Akeneo\Platform\Bundle\FrameworkBundle\Logging\ContextLogProcessor;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class CommandExecutionContextListener
{
    private ContextLogProcessor $contextLogProcessor;

    /**
     * @param ContextLogProcessor $contextLogProcessor
     */
    public function __construct(ContextLogProcessor $contextLogProcessor)
    {
        $this->contextLogProcessor = $contextLogProcessor;
    }


    public function onConsoleCommand(ConsoleCommandEvent $consoleCommandEvent)
    {
        $cmd = $consoleCommandEvent->getCommand();
        $this->contextLogProcessor->initCommandContext($cmd);
    }
}
