<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\EventListener;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Storage event subscriber that update channel locales
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelLocaleSubscriber implements EventSubscriberInterface
{
    /** @var LocaleRepositoryInterface */
    protected $repository;

    /** @var BulkSaverInterface */
    protected $saver;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var string */
    private $jobName;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    public function __construct(
        LocaleRepositoryInterface $repository,
        BulkSaverInterface $saver,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        string $jobName
    ) {
        $this->repository = $repository;
        $this->saver = $saver;
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobName = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeChannel',
            StorageEvents::PRE_SAVE   => 'updateChannel',
        ];
    }

    public function removeChannel(GenericEvent $event)
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $locales = $channel->getLocales();
        $updatedLocales = [];

        foreach ($locales as $locale) {
            $locale->removeChannel($channel);
            $updatedLocales[] = $locale;
        }

        if (!empty($updatedLocales)) {
            $this->saver->saveAll($updatedLocales);
        }
    }

    public function updateChannel(GenericEvent $event)
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $this->updateChannelInBackend($channel);
    }

    /**
     * Update the channel and if at least a locale is removed, it launches the completeness cleaning with a command.
     * @see https://akeneo.atlassian.net/browse/PIM-7155
     */
    private function updateChannelInBackend(ChannelInterface $channel): void
    {
        $oldLocales = $this->repository->getDeletedLocalesForChannel($channel);
        $oldLocalesCodes = array_map(
            function (LocaleInterface $locale) {
                return $locale->getCode();
            },
            $oldLocales
        );
        $updatedLocales = [];

        foreach ($channel->getLocales() as $locale) {
            if (!$locale->hasChannel($channel)) {
                $locale->addChannel($channel);
            }
            $updatedLocales[] = $locale;
        }

        foreach ($oldLocales as $locale) {
            $locale->removeChannel($channel);
            $updatedLocales[] = $locale;
        }

        if (!empty($updatedLocales)) {
            $this->saver->saveAll($updatedLocales);
        }

        if (!empty($oldLocalesCodes)) {
            $this->removeCompletenessForChannelAndLocales($oldLocalesCodes, $channel->getCode());
        }
    }

    private function removeCompletenessForChannelAndLocales(array $localesCodes, string $channelCode): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        $this->jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'locales_identifier' => $localesCodes,
                'channel_code' => $channelCode,
                'username' => $user->getUsername(),
            ]
        );
    }
}
