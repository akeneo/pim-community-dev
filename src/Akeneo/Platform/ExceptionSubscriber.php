<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Platform;

use Google\Cloud\ErrorReporting\Bootstrap;
use Google\Cloud\Logging\PsrLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var PsrLogger */
    protected $psrLogger;

    public function __construct(PsrLogger $psrLogger)
    {
        $this->psrLogger = $psrLogger;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => [
            ['logException', 0]
        ]];
    }

    public function logException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        Bootstrap::init($this->psrLogger);
        Bootstrap::exceptionHandler($exception);
    }
}
