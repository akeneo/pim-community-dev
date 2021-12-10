<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfValuesPerRecord
{
    private const VOLUME_NAME = 'average_max_number_of_values_per_record';

    public function __construct(
        private Connection $sqlConnection,
    ) {
    }

    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
            SELECT
              MAX(JSON_LENGTH(value_collection)) AS max,
              CEIL(AVG(JSON_LENGTH(value_collection))) AS average
            FROM akeneo_reference_entity_record;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();

        return new AverageMaxVolumes(
            (int) $result['max'],
            (int) $result['average'],
            self::VOLUME_NAME
        );
    }
}
