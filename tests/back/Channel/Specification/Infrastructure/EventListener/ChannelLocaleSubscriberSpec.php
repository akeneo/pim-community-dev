<?php

namespace Specification\Akeneo\Channel\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Channel\Infrastructure\EventListener\ChannelLocaleSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ChannelLocaleSubscriberSpec extends ObjectBehavior
{
    public function let(
        LocaleRepositoryInterface $repository,
        BulkSaverInterface $saver,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $user->getUserIdentifier()->willReturn('julia');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith(
            $repository,
            $saver,
            $tokenStorage,
            $jobLauncher,
            $jobInstanceRepository,
            'remove_completeness_for_channel_and_locale'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ChannelLocaleSubscriber::class);
    }

    public function it_subscribes_to_save_events(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_SAVE);
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    public function it_only_handles_channels(BulkSaverInterface $saver): void
    {
        $subject = new \stdClass();
        $saver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->storeUpdatedLocales(new GenericEvent($subject));
        $this->saveLocales(new GenericEvent($subject));
    }

    public function it_saves_locales_related_to_updated_channels(
        LocaleRepositoryInterface $repository,
        BulkSaverInterface $saver,
        JobLauncherInterface $jobLauncher,
    ): void {
        $enUS = new Locale();
        $enUS->setCode('en_US');
        $frFR = new Locale();
        $frFR->setCode('fr_FR');
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $channel->addLocale($enUS);
        $channel->addLocale($frFR);

        $repository->getDeletedLocalesForChannel($channel)->shouldBeCalled()->willReturn([]);
        $saver->saveAll([$enUS, $frFR])->shouldBeCalled();
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->storeUpdatedLocales(new GenericEvent($channel));
        $this->saveLocales(new GenericEvent($channel));
    }

    public function it_saves_locales_removed_from_channels_and_launches_the_clean_completeness_job(
        LocaleRepositoryInterface $repository,
        BulkSaverInterface $saver,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        UserInterface $user,
    ): void {
        $enUS = new Locale();
        $enUS->setCode('en_US');
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $channel->addLocale($enUS);
        $frFR = new Locale();
        $frFR->setCode('fr_FR');
        $deDE = new Locale();
        $deDE->setCode('de_DE');

        $repository->getDeletedLocalesForChannel($channel)->shouldBeCalled()->willReturn([$frFR, $deDE]);
        $saver->saveAll([$enUS, $frFR, $deDE])->shouldBeCalled();

        $jobInstanceRepository->findOneByIdentifier('remove_completeness_for_channel_and_locale')
            ->shouldBeCalled()->willReturn($jobInstance);
        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'locales_identifier' => ['fr_FR', 'de_DE'],
                'channel_code' => 'ecommerce',
                'username' => 'julia',
            ]
        )
            ->shouldBeCalledOnce()
            ->willReturn($jobExecution);

        $this->storeUpdatedLocales(new GenericEvent($channel));
        $this->saveLocales(new GenericEvent($channel));
    }
}
