<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfValuesPerRecord implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_number_of_values_per_record';

    /** @var Connection */
    private $sqlConnection;

    /** @var int */
    private $limit;

    public function __construct(Connection $sqlConnection, int $limit)
    {
        $this->sqlConnection = $sqlConnection;
        $this->limit = $limit;
    }

    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
            SELECT
              MAX(JSON_LENGTH(JSON_EXTRACT(value_collection, '$.*'))) AS max,
              CEIL(AVG(JSON_LENGTH(JSON_EXTRACT(value_collection, '$.*')))) AS average
            FROM akeneo_reference_entity_record;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();
        $volume = new AverageMaxVolumes(
            (int) $result['max'],
            (int) $result['average'],
            $this->limit,
            self::VOLUME_NAME
        );

        return $volume;
    }
}
