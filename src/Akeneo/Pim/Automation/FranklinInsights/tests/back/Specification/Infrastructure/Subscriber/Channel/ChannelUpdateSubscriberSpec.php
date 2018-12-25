<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Channel;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\DeactivateConnectionCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\DeactivateConnectionHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Channel\ChannelUpdateSubscriber;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ChannelUpdateSubscriberSpec extends ObjectBehavior
{
    public function let(LocaleRepositoryInterface $localeRepository, DeactivateConnectionHandler $deactivateConnectionHandler): void
    {
        $this->beConstructedWith($localeRepository, $deactivateConnectionHandler);
    }

    public function it_is_channel_update_subscriber(): void
    {
        $this->shouldHaveType(ChannelUpdateSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_on_post_save_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::POST_SAVE => 'onPostSave']);
    }

    public function it_deactivates_franklin_insights_if_all_english_locales_are_deactivated(
        $localeRepository,
        $deactivateConnectionHandler
    ): void {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['de_DE', 'fr_FR']);
        $deactivateConnectionHandler->handle(new DeactivateConnectionCommand())->shouldBeCalled();

        $this->onPostSave(new GenericEvent(new Channel()));
    }

    public function it_does_nothing_if_an_english_locale_is_activated(
        $localeRepository,
        $deactivateConnectionHandler
    ): void {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['de_DE', 'en_US', 'fr_FR']);
        $deactivateConnectionHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent(new Channel()));
    }

    public function it_does_nothing_if_the_subject_of_the_event_is_not_a_channel(
        $localeRepository,
        $deactivateConnectionHandler
    ): void {
        $localeRepository->getActivatedLocaleCodes()->shouldNotBeCalled();
        $deactivateConnectionHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent(new \StdClass()));
    }
}
