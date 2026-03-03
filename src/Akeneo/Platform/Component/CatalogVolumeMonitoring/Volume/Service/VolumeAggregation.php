<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Service;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Repository\AggregatedVolumeRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Aggregation of catalog volumes, for the queries too expensive to be executed live.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VolumeAggregation
{
    public function __construct(
        private AggregatedVolumeRepositoryInterface $aggregatedVolumeRepository,
        private iterable $countQueries,
        private iterable $averageMaxQueries,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Aggregate all the volumes whose queries should not be executed live.
     */
    public function aggregate(): void
    {
        $this->logger->info('Volumes aggregation in progress.');

        foreach ($this->countQueries as $countQuery) {
            $countVolume = $countQuery->fetch();
            $aggregatedVolume = new AggregatedVolume(
                $countVolume->getVolumeName(),
                ['value' => $countVolume->getVolume()],
                new \DateTime('now', new \DateTimeZone('UTC'))
            );

            $this->aggregatedVolumeRepository->add($aggregatedVolume);
        }

        foreach ($this->averageMaxQueries as $averageMaxQuery) {
            $averageMaxVolume = $averageMaxQuery->fetch();
            $aggregatedVolume = new AggregatedVolume(
                $averageMaxVolume->getVolumeName(),
                [
                    'value' => [
                        'max' => $averageMaxVolume->getMaxVolume(),
                        'average' => $averageMaxVolume->getAverageVolume(),
                    ],
                ],
                new \DateTime('now', new \DateTimeZone('UTC'))
            );

            $this->aggregatedVolumeRepository->add($aggregatedVolume);
        }

        $this->logger->info('Catalog volumes aggregation done.');
    }
}
