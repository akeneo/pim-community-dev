<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxProductModelValues implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_product_model_values';

    /** @var Connection */
    private $connection;

    /** @var int */
    private $limit;

    /**
     * @param Connection $connection
     * @param int        $limit
     */
    public function __construct(Connection $connection, int $limit)
    {
        $this->connection = $connection;
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
            SELECT 
              MAX(JSON_LENGTH(JSON_EXTRACT(raw_values, '$.*.*.*'))) AS max,
              CEIL(AVG(JSON_LENGTH(JSON_EXTRACT(raw_values, '$.*.*.*')))) AS average
            FROM pim_catalog_product_model;
SQL;
        $result = $this->connection->query($sql)->fetch();

        $volume = new AverageMaxVolumes((int)$result['max'], (int)$result['average'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
