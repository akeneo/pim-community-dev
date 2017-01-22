<?php

namespace spec\Pim\Bundle\EnrichBundle\EventListener\Storage;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class ChannelLocaleSubscriberSpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $repository,
        BulkSaverInterface $saver,
        CompletenessGeneratorInterface $completeness
    ) {
        $this->beConstructedWith($repository, $saver, $completeness);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\EventListener\Storage\ChannelLocaleSubscriber');
    }

    function it_subscribes_to_storage_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'removeChannel',
            StorageEvents::PRE_SAVE => 'updateChannel',
        ]);
    }

    function it_does_not_remove_channel_on_non_channels($saver, GenericEvent $event, \stdClass $channel)
    {
        $event->getSubject()->willReturn($channel);
        $saver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->removeChannel($event);
    }

    function it_removes_channel($saver, GenericEvent $event, ChannelInterface $channel, LocaleInterface $locale)
    {
        $event->getSubject()->willReturn($channel);
        $saver->saveAll([$locale])->shouldBeCalled();
        $channel->getLocales()->willReturn([$locale]);
        $locale->removeChannel($channel)->shouldBeCalled();

        $this->removeChannel($event);
    }

    function it_does_not_upadte_channel_on_non_channels($saver, GenericEvent $event, \stdClass $channel)
    {
        $event->getSubject()->willReturn($channel);
        $saver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->updateChannel($event);
    }

    function it_updates_channel(
        $repository,
        $saver,
        $completeness,
        GenericEvent $event,
        ChannelInterface $channel,
        LocaleInterface $localeEn,
        LocaleInterface $localeFr,
        LocaleInterface $localeEs
    ) {
        $event->getSubject()->willReturn($channel);
        $repository->getDeletedLocalesForChannel($channel)->willReturn([$localeEn]);
        // TODO TIP-694: disabling completeness calculation
        // $completeness->scheduleForChannelAndLocale($channel, $localeEn)->shouldBeCalled();

        $localeFr->hasChannel($channel)->willReturn(true);
        $localeEs->hasChannel($channel)->willReturn(false);

        $channel->getLocales()->willReturn([$localeFr, $localeEs]);
        $channel->hasLocale($localeEn)->willReturn(false);
        $channel->hasLocale($localeFr)->willReturn(true);

        $localeEn->removeChannel($channel)->shouldBeCalled();
        $localeEs->addChannel($channel)->shouldBeCalled();

        $saver->saveAll([$localeEn, $localeFr, $localeEs])->shouldBeCalled();

        $this->updateChannel($event);
    }
}
