<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Notifier\InvalidTokenNotifierInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InvalidTokenExceptionSubscriber implements EventSubscriberInterface
{
    private $invalidTokenNotifier;

    public function __construct(InvalidTokenNotifierInterface $invalidTokenNotifier)
    {
        $this->invalidTokenNotifier = $invalidTokenNotifier;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onInvalidTokenException'
        ];
    }

    public function onInvalidTokenException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        if ($exception instanceof InvalidTokenException || $exception->getPrevious() instanceof InvalidTokenException) {
            $this->invalidTokenNotifier->notify();
        }
    }
}
