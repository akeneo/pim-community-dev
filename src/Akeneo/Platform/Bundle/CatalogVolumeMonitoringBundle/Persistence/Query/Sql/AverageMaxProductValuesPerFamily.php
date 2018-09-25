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
class AverageMaxProductValuesPerFamily implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_product_values_per_family';

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
                CEIL(AVG(a.count_product_values_per_families)) average,
                MAX(a.count_product_values_per_families) max
            FROM (
                SELECT
                  f_stats.not_localizable_and_scopable
                    + f_stats.is_only_localizable * f_stats.nb_locales
                    + f_stats.is_only_scopable * f_stats.nb_channels 
                    + f_stats.is_localizable_and_scopable * f_stats.nb_channel_locales as count_product_values_per_families
                FROM (
                    SELECT
                      SUM(not_localizable_and_scopable) as not_localizable_and_scopable,
                      SUM(f.is_only_localizable) as is_only_localizable,
                      SUM(f.is_only_scopable) as is_only_scopable,
                      SUM(f.is_localizable_and_scopable) as is_localizable_and_scopable,
                      (SELECT COUNT(*) as nb_locales FROM pim_catalog_locale WHERE is_activated = 1) as nb_locales,
                      (SELECT COUNT(*) as nb_channels FROM pim_catalog_channel) as nb_channels,
                      (SELECT COUNT(*) as nb_channel_locales FROM pim_catalog_channel_locale) as nb_channel_locales
                    FROM (
                        SELECT
                          f.code,
                          (a.is_localizable = 0 AND a.is_scopable = 0) as not_localizable_and_scopable,
                          (a.is_scopable = 0 AND a.is_localizable = 1) as is_only_localizable,
                          (a.is_scopable = 1 AND a.is_localizable = 0) as is_only_scopable,
                          (a.is_localizable = 1 AND a.is_scopable = 1) as is_localizable_and_scopable
                        FROM pim_catalog_family f
                        JOIN pim_catalog_family_attribute fa on fa.family_id = f.id
                        JOIN pim_catalog_attribute a on a.id = fa.attribute_id
                        ORDER BY f.code
                    ) as f
                    GROUP BY f.code
                ) as f_stats
            ) as a
SQL;
        $result = $this->connection->query($sql)->fetch();
        $volume = new AverageMaxVolumes((int) $result['max'], (int) $result['average'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
