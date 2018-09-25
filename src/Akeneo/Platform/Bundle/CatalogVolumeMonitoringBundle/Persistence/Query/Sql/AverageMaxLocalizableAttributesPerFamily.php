<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

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
                CEIL(AVG(count_only_localizable_attributes * 100 / count_attributes)) average,
                CEIL(MAX(count_only_localizable_attributes * 100 / count_attributes)) max
            FROM (
                SELECT fa.family_id as family_id, SUM(a.is_localizable = 1 AND a.is_scopable = 0) as count_only_localizable_attributes, COUNT(a.code) as count_attributes
                FROM pim_catalog_family_attribute as fa
                INNER JOIN pim_catalog_attribute as a ON fa.attribute_id = a.id
                GROUP BY fa.family_id
            ) as attr;
SQL;
        $result = $this->connection->query($sql)->fetch();
        $volume = new AverageMaxVolumes((int) $result['max'], (int) $result['average'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
