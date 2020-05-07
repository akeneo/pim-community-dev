<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * Retrieve all hours from events that are not yet complete.
 * I.e., the last update happened before the end of the event and need to be updated again.
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectHourlyIntervalsToRefreshQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @return HourlyInterval[]
     */
    public function execute(): array
    {
        $selectSQL = <<<SQL
SELECT DISTINCT event_datetime FROM akeneo_connectivity_connection_audit_product
WHERE updated < DATE_ADD(event_datetime, INTERVAL 1 HOUR) ORDER BY event_datetime
SQL;
        $dateTimes = $this->dbalConnection->executeQuery($selectSQL)->fetchAll(FetchMode::COLUMN);

        return array_map(function (string $dateTime) {
            return HourlyInterval::createFromDateTime(
                \DateTimeImmutable::createFromFormat(
                    $this->dbalConnection->getDatabasePlatform()->getDateTimeFormatString(),
                    $dateTime,
                    new \DateTimeZone('UTC')
                )
            );
        }, $dateTimes);
    }
}
