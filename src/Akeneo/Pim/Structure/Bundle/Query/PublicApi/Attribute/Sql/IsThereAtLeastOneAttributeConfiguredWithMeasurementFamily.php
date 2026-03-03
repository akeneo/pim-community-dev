<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $metricFamilyCode): bool
    {
        $query = <<<SQL
SELECT 1
FROM pim_catalog_attribute
WHERE metric_family = :metric_family
AND attribute_type = 'pim_catalog_metric';
SQL;
        $stmt = $this->connection->executeQuery($query, ['metric_family' => $metricFamilyCode]);
        return $this->connection->convertToPHPValue($stmt->fetch(\PDO::FETCH_COLUMN), Types::BOOLEAN);
    }
}
