<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\ExtractConnectionsProductEventCountQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalExtractConnectionsProductEventCountQuery implements ExtractConnectionsProductEventCountQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    /** @var string */
    private $productClass;

    public function __construct(DbalConnection $dbalConnection, string $productClass)
    {
        $this->dbalConnection = $dbalConnection;
        $this->productClass = $productClass;
    }

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
        )->fetchAll();

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

    public function extractAllCreatedProducts(HourlyInterval $hourlyInterval): array
    {
        $sqlQuery = <<<SQL
SELECT event_count
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
        )->fetchAll();

        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new HourlyEventCount(
                AllConnectionCode::CODE,
                $hourlyInterval,
                (int) $dataRow['event_count'],
                EventTypes::PRODUCT_CREATED
            );
        }

        return $dailyEventCount;
    }

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
        )->fetchAll();

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

    public function extractAllUpdatedProducts(HourlyInterval $hourlyInterval): array
    {
        $sqlQuery = <<<SQL
SELECT event_count
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
        )->fetchAll();

        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new HourlyEventCount(
                AllConnectionCode::CODE,
                $hourlyInterval,
                (int) $dataRow['event_count'],
                EventTypes::PRODUCT_UPDATED
            );
        }

        return $dailyEventCount;
    }
}
