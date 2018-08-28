<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

/**
 * @author    Elodie Rapos <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxLocalizableAttributesPerFamily implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_localizable_attributes_per_family';

    /** @var Connection */
    private $connection;

    /** @var int */
    private $limit;

    /**
     * @param Connection $connection
     * @param int $limit
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
                CEIL(AVG(a.count_attributes_per_family)) average,
                MAX(a.count_attributes_per_family) max
            FROM (
                SELECT count(fa.attribute_id) count_attributes_per_family 
                FROM pim_catalog_family_attribute as fa
                INNER JOIN pim_catalog_attribute as a ON fa.attribute_id = a.id
                WHERE a.is_localizable = 1 AND a.is_scopable = 0
                GROUP BY fa.family_id
            ) a
SQL;
        $result = $this->connection->query($sql)->fetch();
        $volume = new AverageMaxVolumes((int) $result['max'], (int) $result['average'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
