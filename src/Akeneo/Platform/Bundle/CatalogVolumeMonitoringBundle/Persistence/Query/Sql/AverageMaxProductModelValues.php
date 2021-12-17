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

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
        $result = $this->connection->executeQuery($sql)->fetchAssociative();

        $volume = new AverageMaxVolumes((int)$result['max'], (int)$result['average'], self::VOLUME_NAME);

        return $volume;
    }
}
