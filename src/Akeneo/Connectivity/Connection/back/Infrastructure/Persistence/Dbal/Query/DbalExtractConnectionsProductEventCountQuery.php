<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\DailyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\ExtractConnectionsProductEventCountQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectVersioningProductEventCountByDayQuery;
use Doctrine\DBAL\Connection as DbalConnection;

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

    public function extractCreatedProductsByConnection(string $date): array
    {
        $dateTime = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        $dateTime->setTime(0, 0, 0, 0);

        $sqlQuery = <<<SQL
SELECT conn.code, COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id 
    FROM pim_versioning_version USE INDEX(logged_at_idx) 
    WHERE logged_at >= :start_time AND logged_at < :end_time 
    AND resource_name = :resource_name
    AND version = 1 
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = :user_type
INNER JOIN akeneo_connectivity_connection conn ON conn.user_id = u.id
GROUP BY conn.code;
SQL;
        $sqlParams = [
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time' => $dateTime->modify('+1 day')->format('Y-m-d H:i:s'),
            'resource_name' => $this->productClass,
            'user_type' => \Akeneo\UserManagement\Component\Model\User::TYPE_API
        ];

        $dataRows = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();
        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new DailyEventCount(
                $dataRow['code'],
                $dateTime->format('Y-m-d'),
                (int)$dataRow['event_count'],
                EventTypes::PRODUCT_CREATED
            );
        }

        return $dailyEventCount;
    }

    public function extractAllCreatedProducts(string $date): array
    {
        $dateTime = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        $dateTime->setTime(0, 0, 0, 0);

        $sqlQuery = <<<SQL
SELECT COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id 
    FROM pim_versioning_version USE INDEX(logged_at_idx) 
    WHERE logged_at >= :start_time AND logged_at < :end_time 
    AND resource_name = :resource_name
    AND version = 1 
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = :user_type
SQL;
        $sqlParams = [
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time'   => $dateTime->modify('+1 day')->format('Y-m-d H:i:s'),
            'resource_name' => $this->productClass,
            'user_type' => \Akeneo\UserManagement\Component\Model\User::TYPE_API
        ];

        $dataRows = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();
        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new DailyEventCount(
                '<all>',
                $dateTime->format('Y-m-d'),
                (int) $dataRow['event_count'],
                EventTypes::PRODUCT_CREATED
            );
        }

        return $dailyEventCount;
    }

    public function extractUpdatedProductsByConnection(string $date): array
    {
        $dateTime = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        $dateTime->setTime(0, 0, 0, 0);

        $sqlQuery = <<<SQL
SELECT conn.code, COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id 
    FROM pim_versioning_version USE INDEX(logged_at_idx) 
    WHERE logged_at >= :start_time AND logged_at < :end_time 
    AND resource_name = :resource_name
    AND version != 1 
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = :user_type
INNER JOIN akeneo_connectivity_connection conn ON conn.user_id = u.id
GROUP BY conn.code;
SQL;
        $sqlParams = [
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time'   => $dateTime->modify('+1 day')->format('Y-m-d H:i:s'),
            'resource_name' => $this->productClass,
            'user_type' => \Akeneo\UserManagement\Component\Model\User::TYPE_API
        ];

        $dataRows = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();
        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new DailyEventCount(
                $dataRow['code'],
                $dateTime->format('Y-m-d'),
                (int) $dataRow['event_count'],
                EventTypes::PRODUCT_UPDATED
            );
        }

        return $dailyEventCount;
    }

    public function extractAllUpdatedProducts(string $date): array
    {
        $dateTime = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        $dateTime->setTime(0, 0, 0, 0);

        $sqlQuery = <<<SQL
SELECT COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id 
    FROM pim_versioning_version USE INDEX(logged_at_idx) 
    WHERE logged_at >= :start_time AND logged_at < :end_time 
    AND resource_name = :resource_name
    AND version != 1 
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = :user_type
SQL;
        $sqlParams = [
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time'   => $dateTime->modify('+1 day')->format('Y-m-d H:i:s'),
            'resource_name' => $this->productClass,
            'user_type' => \Akeneo\UserManagement\Component\Model\User::TYPE_API
        ];

        $dataRows = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();
        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new DailyEventCount(
                '<all>',
                $dateTime->format('Y-m-d'),
                (int) $dataRow['event_count'],
                EventTypes::PRODUCT_UPDATED
            );
        }

        return $dailyEventCount;
    }
}
