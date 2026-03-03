<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\ExtractConnectionsProductEventCountQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalExtractConnectionsProductEventCountQuery implements ExtractConnectionsProductEventCountQueryInterface
{
    public function __construct(private DbalConnection $dbalConnection, private string $productClass)
    {
    }

    /**
     * @return HourlyEventCount[]
     */
    public function extractCreatedProductsByConnection(HourlyInterval $hourlyInterval): array
    {
        $sqlQuery = <<<SQL
SELECT conn.code, event_count
FROM (
    SELECT author, COUNT(id) as event_count
    FROM pim_versioning_version USE INDEX(logged_at_idx)
    WHERE logged_at >= :start_time AND logged_at < :end_time
    AND resource_name = :resource_name
    AND version = 1
    GROUP BY author
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = :user_type
INNER JOIN akeneo_connectivity_connection conn ON conn.user_id = u.id
WHERE conn.auditable = 1 AND conn.flow_type = :flow_type;
SQL;

        $dataRows = $this->dbalConnection->executeQuery(
            $sqlQuery,
            [
                'start_time' => $hourlyInterval->fromDateTime(),
                'end_time' => $hourlyInterval->upToDateTime(),
                'resource_name' => $this->productClass,
                'user_type' => User::TYPE_API,
                'flow_type' => FlowType::DATA_SOURCE,
        ],
            [
                'start_time' => Types::DATETIME_IMMUTABLE,
                'end_time' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAllAssociative();

        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new HourlyEventCount(
                $dataRow['code'],
                $hourlyInterval,
                (int) $dataRow['event_count'],
                EventTypes::PRODUCT_CREATED
            );
        }

        return $dailyEventCount;
    }

    /**
     * @return HourlyEventCount[]
     */
    public function extractUpdatedProductsByConnection(HourlyInterval $hourlyInterval): array
    {
        $sqlQuery = <<<SQL
SELECT conn.code, event_count
FROM (
    SELECT author, COUNT(id) as event_count
    FROM pim_versioning_version USE INDEX(logged_at_idx)
    WHERE logged_at >= :start_time AND logged_at < :end_time
    AND resource_name = :resource_name
    AND version != 1
    GROUP BY author
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = :user_type
INNER JOIN akeneo_connectivity_connection conn ON conn.user_id = u.id
WHERE conn.auditable = 1 AND conn.flow_type = :flow_type
SQL;
        $dataRows = $this->dbalConnection->executeQuery(
            $sqlQuery,
            [
                'start_time' => $hourlyInterval->fromDateTime(),
                'end_time' => $hourlyInterval->upToDateTime(),
                'resource_name' => $this->productClass,
                'user_type' => User::TYPE_API,
                'flow_type' => FlowType::DATA_SOURCE,
        ],
            [
                'start_time' => Types::DATETIME_IMMUTABLE,
                'end_time' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAllAssociative();

        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new HourlyEventCount(
                $dataRow['code'],
                $hourlyInterval,
                (int) $dataRow['event_count'],
                EventTypes::PRODUCT_UPDATED
            );
        }

        return $dailyEventCount;
    }
}
