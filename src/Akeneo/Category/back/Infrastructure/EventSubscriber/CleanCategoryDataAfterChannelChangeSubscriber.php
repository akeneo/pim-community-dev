<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CleanCategoryDataAfterChannelChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JobInstanceRepository $jobInstanceRepository,
        private readonly JobLauncherInterface $jobLauncher,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'cleanCategoryDataForChannelLocale',
            StorageEvents::POST_REMOVE => 'cleanCategoryDataForChannel',
        ];
    }

    public function cleanCategoryDataForChannel(GenericEvent $event): void
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        /** @var JobInstance|null $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values');
        if (!$jobInstance instanceof JobInstance) {
            return;
        }
        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()?->getUser(), [
            'channel_code' => $channel->getCode(),
            'locales_codes' => [],
        ]);
    }

    public function cleanCategoryDataForChannelLocale(GenericEvent $event): void
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        /** @var JobInstance|null $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values');
        if (!$jobInstance instanceof JobInstance) {
            return;
        }

        $locales = array_map(static function (Locale $locale) {
            return $locale->getCode();
        }, $channel->getLocales()->getValues());

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()?->getUser(), [
            'channel_code' => $channel->getCode(),
            'locales_codes' => $locales,
        ]);
    }
}
