<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\CatalogVolumeMonitoring;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfRecordsPerReferenceEntity;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxNumberOfRecordsPerReferenceEntity implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_records_per_reference_entity';

    /** @var SqlAverageMaxNumberOfRecordsPerReferenceEntity */
    private $averageMaxNumberOfRecordsPerReferenceEntity;

    /** @var int */
    private $limit;

    public function __construct(
        SqlAverageMaxNumberOfRecordsPerReferenceEntity $averageMaxNumberOfRecordsPerReferenceEntity,
        int $limit
    ) {
        $this->averageMaxNumberOfRecordsPerReferenceEntity = $averageMaxNumberOfRecordsPerReferenceEntity;
        $this->limit = $limit;
    }

    public function fetch(): AverageMaxVolumes
    {
        $volume = $this->averageMaxNumberOfRecordsPerReferenceEntity->fetch();
        $result = new AverageMaxVolumes(
            $volume->getMaxVolume(),
            $volume->getAverageVolume(),
            $this->limit,
            self::VOLUME_NAME
        );

        return $result;
    }
}
