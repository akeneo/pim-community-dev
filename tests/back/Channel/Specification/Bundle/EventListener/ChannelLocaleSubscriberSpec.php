<?php

namespace Specification\Akeneo\Channel\Bundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Bundle\EventListener\ChannelLocaleSubscriber;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ChannelLocaleSubscriberSpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $repository,
        BulkSaverInterface $saver,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository
    ) {
        $this->beConstructedWith(
            $repository,
            $saver,
            $jobLauncher,
            $tokenStorage,
            $jobInstanceRepository,
            'remove_completeness_for_channel_and_locale'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelLocaleSubscriber::class);
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

    function it_updates_channel_when_a_locale_has_been_removed(
        $repository,
        $saver,
        $tokenStorage,
        UserInterface $user,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $jobInstance,
        JobLauncherInterface $jobLauncher,
        GenericEvent $event,
        ChannelInterface $channel,
        LocaleInterface $localeEn,
        LocaleInterface $localeFr,
        LocaleInterface $localeEs,
        TokenInterface $token
    ) {
        $event->getSubject()->willReturn($channel);
        $repository->getDeletedLocalesForChannel($channel)->willReturn([$localeEn]);

        $localeEn->getCode()->willReturn('en_US');
        $localeFr->hasChannel($channel)->willReturn(true);
        $localeEs->hasChannel($channel)->willReturn(false);

        $channel->getLocales()->willReturn([$localeFr, $localeEs]);
        $channel->getCode()->willReturn('print');
        $channel->hasLocale($localeEn)->willReturn(false);
        $channel->hasLocale($localeFr)->willReturn(true);

        $localeEn->removeChannel($channel)->shouldBeCalled();
        $localeEs->addChannel($channel)->shouldBeCalled();

        $saver->saveAll([$localeFr, $localeEs, $localeEn])->shouldBeCalled();

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');

        $jobInstanceRepository
            ->findOneByIdentifier('remove_completeness_for_channel_and_locale')
            ->willReturn($jobInstance);

        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'locales_identifier' => ['en_US'],
                'channel_code' => 'print',
                'username' => 'julia',
            ]
        )->shouldBeCalled();

        $this->updateChannel($event);
    }

    function it_updates_channel_without_removed_locale(
        $repository,
        $saver,
        GenericEvent $event,
        ChannelInterface $channel,
        LocaleInterface $localeFr,
        LocaleInterface $localeEs,
        JobLauncherInterface $jobLauncher
    ) {
        $event->getSubject()->willReturn($channel);
        $repository->getDeletedLocalesForChannel($channel)->willReturn([]);

        $localeFr->hasChannel($channel)->willReturn(true);
        $localeEs->hasChannel($channel)->willReturn(false);

        $channel->getLocales()->willReturn([$localeFr, $localeEs]);
        $channel->getCode()->willReturn('print');
        $channel->hasLocale($localeFr)->willReturn(true);

        $localeEs->addChannel($channel)->shouldBeCalled();

        $saver->saveAll([$localeFr, $localeEs])->shouldBeCalled();

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->updateChannel($event);
    }
}
