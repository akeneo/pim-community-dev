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

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\GetChannelActiveLocaleCodes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Store the channel original locale codes during the pre-save events
 * and remove the channel deleted locales in all exports during the post-save events
 */
class RemoveLocaleFilterInJobInstancesSubscriber implements EventSubscriberInterface
{
    private $localeCodesByChannel = [];

    /** @var GetChannelActiveLocaleCodes */
    private $getChannelLocaleCodes;

    /** @var ObjectRepository */
    private $jobInstanceRepository;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    public function __construct(GetChannelActiveLocaleCodes $getChannelLocaleCodes, ObjectRepository $jobInstanceRepository, BulkSaverInterface $bulkSaver)
    {
        $this->getChannelLocaleCodes = $getChannelLocaleCodes;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->bulkSaver = $bulkSaver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'storeChannelLocaleCodes',
            StorageEvents::PRE_SAVE_ALL => 'storeChannelLocaleCodes',
            StorageEvents::POST_SAVE => 'removeDeletedLocalesFromJobInstancesFilters',
            StorageEvents::POST_SAVE_ALL => 'removeDeletedLocalesFromJobInstancesFilters',
        ];
    }

    public function storeChannelLocaleCodes(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if ($event->getSubject() instanceof ChannelInterface) {
            if ($subject->getId() !== null) {
                $this->localeCodesByChannel[$subject->getCode()] = $this->getChannelLocaleCodes->execute($subject->getCode());
            }

            return;
        }

        if (is_array($subject) && current($subject) instanceof ChannelInterface) {
            foreach ($subject as $channel) {
                if ($channel->getId() !== null) {
                    $this->localeCodesByChannel[$channel->getCode()] = $this->getChannelLocaleCodes->execute($channel->getCode());
                }
            }
        }
    }

    public function removeDeletedLocalesFromJobInstancesFilters(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if ($event->getSubject() instanceof ChannelInterface) {
            $this->removeChannelDeletedLocales($subject);
        }

        if (is_array($subject) && current($subject) instanceof ChannelInterface) {
            foreach ($subject as $channel) {
                $this->removeChannelDeletedLocales($channel);
            }
        }
    }

    private function removeChannelDeletedLocales(ChannelInterface $channel): void
    {
        if (isset($this->localeCodesByChannel[$channel->getCode()])) {
            $channelLocaleCodes = $channel->getLocaleCodes();
            sort($channelLocaleCodes);
            $removedLocaleCodes = array_diff($this->localeCodesByChannel[$channel->getCode()], $channelLocaleCodes);
            if (!empty($removedLocaleCodes)) {
                $this->updateJobInstancesFilters($channel->getCode(), $removedLocaleCodes);
            }
        }
    }

    private function updateJobInstancesFilters(string $channelCode, array $removedLocaleCodes): void
    {
        $jobsToUpdate = [];

        foreach ($this->jobInstanceRepository->findBy(['type' => JobInstance::TYPE_EXPORT]) as $jobInstance) {
            $rawParameters = $jobInstance->getRawParameters();
            if (
                isset($rawParameters['filters']['structure']['locales']) &&
                isset($rawParameters['filters']['structure']['scope']) &&
                $channelCode === $rawParameters['filters']['structure']['scope']
            ) {
                $jobLocales = $rawParameters['filters']['structure']['locales'];
                sort($jobLocales);
                $newLocaleCodes = array_diff($jobLocales, $removedLocaleCodes);
                $rawParameters['filters']['structure']['locales'] = $newLocaleCodes;
                $jobInstance->setRawParameters($rawParameters);

                $jobsToUpdate[] = $jobInstance;
            }
        }

        if (!empty($jobsToUpdate)) {
            $this->bulkSaver->saveAll($jobsToUpdate);
        }
    }
}
