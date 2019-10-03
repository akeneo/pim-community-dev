<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave;

use Akeneo\Channel\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTree implements EventSubscriberInterface
{
    /** @var ObjectRepository */
    private $jobInstanceRepository;

    /** @var ObjectUpdaterInterface */
    private $jobInstanceUpdater;

    /** @var BulkSaverInterface */
    private $jobInstanceSaver;

    /** @var array|string[] */
    private $supportedJobNames;

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelCategoryHasBeenUpdated::class => 'onChannelCategoryHasBeenUpdatedEvent',
        ];
    }

    /**
     * @param array|string[] $supportedJobNames
     */
    public function __construct(
        ObjectRepository $jobInstanceRepository,
        ObjectUpdaterInterface $jobInstanceUpdater,
        BulkSaverInterface $jobInstanceSaver,
        array $supportedJobNames
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobInstanceUpdater = $jobInstanceUpdater;
        $this->jobInstanceSaver = $jobInstanceSaver;
        $this->supportedJobNames = $supportedJobNames;
    }

    public function onChannelCategoryHasBeenUpdatedEvent(ChannelCategoryHasBeenUpdated $event): void
    {
        $this->updateExports($event->channelCode(), $event->newCategoryCode());
    }

    private function updateExports(string $channelCode, string $categoryCode): void
    {
        $jobInstances = $this->findJobInstancesByChannel($channelCode);

        foreach ($jobInstances as $jobInstance) {
            $parameters = $jobInstance->getRawParameters();
            $parameters = $this->replaceCategoriesFilter($parameters, $categoryCode);

            $this->jobInstanceUpdater->update($jobInstance, ['configuration' => $parameters]);
        }

        $this->jobInstanceSaver->saveAll($jobInstances);
    }

    /**
     * @return JobInstance[]
     */
    private function findJobInstancesByChannel(string $channelCode): array
    {
        $jobInstances = $this->jobInstanceRepository->findBy(
            [
                'jobName' => $this->supportedJobNames,
            ]
        );

        return \array_filter(
            $jobInstances,
            function (JobInstance $jobInstance) use ($channelCode) {
                return $jobInstance->getRawParameters()['filters']['structure']['scope'] === $channelCode;
            }
        );
    }

    private function replaceCategoriesFilter(array $parameters, string $categoryCode): array
    {
        $parameters['filters']['data'] = \array_map(
            function ($data) use ($categoryCode) {
                if ($data['field'] === 'categories') {
                    return [
                        'field' => 'categories',
                        'operator' => $data['operator'],
                        'value' => [$categoryCode],
                    ];
                }

                return $data;
            },
            $parameters['filters']['data']
        );

        return $parameters;
    }
}
