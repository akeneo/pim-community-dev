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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Notifier\InvalidTokenNotifierInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Exception\InvalidTokenExceptionSubscriber;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class InvalidTokenExceptionSubscriberSpec extends ObjectBehavior
{
    public function let(InvalidTokenNotifierInterface $invalidTokenNotifier)
    {
        $this->beConstructedWith($invalidTokenNotifier);
    }

    public function it_is_the_invalid_token_exception_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(InvalidTokenExceptionSubscriber::class);
    }

    public function it_notifies_if_an_invalid_token_exception_is_thrown(
        InvalidTokenNotifierInterface $invalidTokenNotifier,
        GetResponseForExceptionEvent $event
    ) {
        $event->getException()->willReturn(new InvalidTokenException());
        $invalidTokenNotifier->notify()->shouldBeCalled();

        $this->onInvalidTokenException($event);
    }

    public function it_notifies_if_an_nested_invalid_token_exception_is_thrown(
        InvalidTokenNotifierInterface $invalidTokenNotifier,
        GetResponseForExceptionEvent $event
    ) {
        $event->getException()->willReturn(DataProviderException::authenticationError(new InvalidTokenException()));
        $invalidTokenNotifier->notify()->shouldBeCalled();

        $this->onInvalidTokenException($event);
    }

    public function it_does_nothing_if_another_exception_than_data_provider_is_thrown(
        InvalidTokenNotifierInterface $invalidTokenNotifier,
        GetResponseForExceptionEvent $event
    ) {
        $event->getException()->willReturn(new \Exception());
        $invalidTokenNotifier->notify()->shouldNotBeCalled();

        $this->onInvalidTokenException($event);
    }

    public function it_does_nothing_if_another_exception_than_invalid_token_is_thrown(
        InvalidTokenNotifierInterface $invalidTokenNotifier,
        GetResponseForExceptionEvent $event
    ) {
        $event->getException()->willReturn(DataProviderException::authenticationError(new \Exception()));
        $invalidTokenNotifier->notify()->shouldNotBeCalled();

        $this->onInvalidTokenException($event);
    }
}
