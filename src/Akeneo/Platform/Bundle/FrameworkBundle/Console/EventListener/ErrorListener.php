<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\FrameworkBundle\Console\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author JMLeroux <jean-marie.leroux@akeneo.com>
 */
class ErrorListener implements EventSubscriberInterface
{
    private ?LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        if (null === $this->logger) {
            return;
        }

        $error = $event->getError();

        if (!$inputString = $this->getInputString($event)) {
            $this->logger->critical(
                'An error occurred while using the console. Message: "{message}"',
                [
                    'exception' => $error,
                    'message' => $error->getMessage(),
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15),
                ]
            );

            return;
        }

        $this->logger->critical(
            'Error thrown while running command "{command}". Message: "{message}"',
            [
                'exception' => $error,
                'command' => $inputString,
                'message' => $error->getMessage(),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15),
            ]
        );
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        if (null === $this->logger) {
            return;
        }

        $exitCode = $event->getExitCode();

        if (0 === $exitCode) {
            return;
        }

        if (!$inputString = $this->getInputString($event)) {
            $this->logger->debug('The console exited with code "{code}"', ['code' => $exitCode]);

            return;
        }

        $this->logger->debug(
            'Command "{command}" exited with code "{code}"',
            ['command' => $inputString, 'code' => $exitCode]
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::ERROR => ['onConsoleError', -128],
            ConsoleEvents::TERMINATE => ['onConsoleTerminate', -128],
        ];
    }

    private static function getInputString(ConsoleEvent $event): ?string
    {
        $commandName = $event->getCommand() ? $event->getCommand()->getName() : null;
        $input = $event->getInput();

        if (method_exists($input, '__toString')) {
            if ($commandName) {
                return str_replace(["'$commandName'", "\"$commandName\""], $commandName, (string)$input);
            }

            return (string)$input;
        }

        return $commandName;
    }
}
