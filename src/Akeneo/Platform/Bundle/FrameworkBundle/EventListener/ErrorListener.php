<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener as SymfonyErrorListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ErrorListener extends SymfonyErrorListener
{
    public function __construct(
        $controller,
        LoggerInterface $logger = null,
        $debug = false
    ) {
        parent::__construct($controller, $logger, $debug);
    }

    public function logKernelException(ExceptionEvent $event)
    {
        $e = FlattenException::createFromThrowable($event->getThrowable());

        $this->logExceptionWithContext(
            $event->getThrowable(),
            sprintf(
                'Uncaught PHP Exception %s: "%s" at %s line %s',
                $e->getClass(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            )
        );
    }

    protected function logExceptionWithContext(
        \Throwable $exception,
        string $message
    ): void {
        if (null !== $this->logger) {
            $logContext = [
                'exception' => $exception,
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15),
            ];
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, $logContext);
            } else {
                $this->logger->notice($message, $logContext);
            }
        }
    }

    protected function logException(\Throwable $exception, string $message): void
    {
        $this->logExceptionWithContext($exception, $message);
    }
}
