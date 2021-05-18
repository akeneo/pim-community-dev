<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfValuesPerRecord
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
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
        $volume = new AverageMaxVolumes(
            (int) $result['max'],
            (int) $result['average']
        );

        return $volume;
    }
}
