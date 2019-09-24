<?php

namespace Akeneo\Channel\Bundle\EventListener;

use Akeneo\Channel\Component\Event\ChannelLocalesHaveBeenUpdated;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigureLocalesForExportJobsAfterChangingTheChannelLocales implements EventSubscriberInterface
{
    /** @var ObjectRepository */
    private $jobInstanceRepository;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    public function __construct(ObjectRepository $jobInstanceRepository, BulkSaverInterface $bulkSaver) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->bulkSaver = $bulkSaver;
    }

    public static function getSubscribedEvents()
    {
        return [
            ChannelLocalesHaveBeenUpdated::class => 'onChannelLocalesHaveBeenUpdated',
        ];
    }

    public function onChannelLocalesHaveBeenUpdated(ChannelLocalesHaveBeenUpdated $event): void
    {
        $jobsToUpdate = [];
        foreach ($this->jobInstanceRepository->findBy(['type' => JobInstance::TYPE_EXPORT]) as $jobInstance) {
            $rawParameters = $jobInstance->getRawParameters();
            if (
                isset($rawParameters['filters']['structure']['locales']) &&
                isset($rawParameters['filters']['structure']['scope']) &&
                $event->channelCode() === $rawParameters['filters']['structure']['scope']
            ) {
                $jobLocales = $rawParameters['filters']['structure']['locales'];
                sort($jobLocales);
                $newLocaleCodes = array_diff($jobLocales, $event->deletedLocaleCodes());
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
