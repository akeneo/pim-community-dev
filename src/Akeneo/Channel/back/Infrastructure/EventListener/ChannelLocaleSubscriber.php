<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Storage event subscriber that updates channel locales
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LocaleRepositoryInterface $repository,
        private readonly BulkSaverInterface $saver,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JobLauncherInterface $jobLauncher,
        private readonly IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private readonly string $jobName,
        private array $updatedLocales = [],
        private array $localesRemovedFromChannel = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'storeUpdatedLocales',
            StorageEvents::POST_SAVE => 'saveLocales',
        ];
    }

    public function storeUpdatedLocales(GenericEvent $event): void
    {
        $channel = $event->getSubject();
        if (!$channel instanceof ChannelInterface) {
            return;
        }

        foreach ($channel->getLocales() as $locale) {
            if (!$locale->hasChannel($channel)) {
                $locale->addChannel($channel);
            }
            $this->updatedLocales[$channel->getCode()][$locale->getCode()] = $locale;
        }

        $oldLocales = $this->repository->getDeletedLocalesForChannel($channel);
        foreach ($oldLocales as $locale) {
            if ($locale->hasChannel($channel)) {
                $locale->removeChannel($channel);
            }
            $this->updatedLocales[$channel->getCode()][$locale->getCode()] = $locale;
            $this->localesRemovedFromChannel[$channel->getCode()][$locale->getCode()] = $locale->getCode();
        }
    }

    public function saveLocales(GenericEvent $event): void
    {
        $channel = $event->getSubject();
        if (!$channel instanceof ChannelInterface || !isset($this->updatedLocales[$channel->getCode()])) {
            return;
        }
        $this->saver->saveAll(array_values($this->updatedLocales[$channel->getCode()]));
        $this->removeCompletenessForChannelAndLocales($channel->getCode());
    }

    private function removeCompletenessForChannelAndLocales(string $channelCode): void
    {
        if (!isset($this->localesRemovedFromChannel[$channelCode])) {
            return;
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        $this->jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'locales_identifier' => array_values($this->localesRemovedFromChannel[$channelCode]),
                'channel_code' => $channelCode,
                'username' => $user->getUserIdentifier(),
            ]
        );
    }
}
