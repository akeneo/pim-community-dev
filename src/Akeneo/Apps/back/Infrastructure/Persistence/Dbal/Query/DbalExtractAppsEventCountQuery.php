<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Domain\Audit\Model\Write\DailyEventCount;
use Akeneo\Apps\Domain\Audit\Persistence\Query\ExtractAppsEventCountQuery;
use Akeneo\Apps\Domain\Audit\Persistence\Query\SelectVersioningProductEventCountByDayQuery;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalExtractAppsEventCountQuery implements ExtractAppsEventCountQuery
{
    /** @var Connection */
    private $dbalConnection;
    /** @var string */
    private $productClass;

    public function __construct(Connection $dbalConnection, string $productClass)
    {
        $this->dbalConnection = $dbalConnection;
        $this->productClass = $productClass;
    }

    public function extractCreatedProducts(string $date): array
    {
        $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
        $dateTime->setTime(0, 0, 0, 0);

        $sqlQuery = <<<SQL
SELECT app.code, COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id 
    FROM pim_versioning_version USE INDEX(logged_at_idx) 
    WHERE logged_at >= :start_time AND logged_at < :end_time 
    AND resource_name = :resource_name
    AND version = 1 
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = "app"
INNER JOIN akeneo_app app ON app.user_id = u.id
GROUP BY app.code;
SQL;
        $sqlParams = [
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time'   => $dateTime->modify('+1 day')->format('Y-m-d H:i:s'),
            'resource_name' => $this->productClass
        ];

        $dataRows = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();
        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new DailyEventCount(
                $dataRow['code'],
                $dateTime->format('Y-m-d'),
                (int) $dataRow['event_count'],
                'product_created'
            );
        }

        return $dailyEventCount;
    }

    public function extractUpdatedProducts(string $date): array
    {
        $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
        $dateTime->setTime(0, 0, 0, 0);

        $sqlQuery = <<<SQL
SELECT app.code, COUNT(resource_id) as event_count
FROM (
    SELECT author, resource_id 
    FROM pim_versioning_version USE INDEX(logged_at_idx) 
    WHERE logged_at >= :start_time AND logged_at < :end_time 
    AND resource_name = :resource_name
    AND version != 1 
    GROUP BY author, resource_id
) AS tmp_table
INNER JOIN oro_user u ON u.username = author AND u.user_type = "app"
INNER JOIN akeneo_app app ON app.user_id = u.id
GROUP BY app.code;
SQL;
        $sqlParams = [
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time'   => $dateTime->modify('+1 day')->format('Y-m-d H:i:s'),
            'resource_name' => $this->productClass
        ];

        $dataRows = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();
        $dailyEventCount = [];
        foreach ($dataRows as $dataRow) {
            $dailyEventCount[] = new DailyEventCount(
                $dataRow['code'],
                $dateTime->format('Y-m-d'),
                (int) $dataRow['event_count'],
                'product_updated'
            );
        }

        return $dailyEventCount;
    }
}
