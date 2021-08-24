<?php


namespace Akeneo\Tool\Component\Batch\Event;

use Monolog\ErrorHandler;
use Monolog\Logger;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ErrorHandlerConfigurationListener
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onConsoleCommand(ConsoleCommandEvent $consoleCommandEvent)
    {
        $handler = new ErrorHandler($this->logger);
        $handler->registerErrorHandler([], false);
        $handler->registerExceptionHandler(Logger::CRITICAL, false);
        $handler->registerFatalHandler();
    }
}
