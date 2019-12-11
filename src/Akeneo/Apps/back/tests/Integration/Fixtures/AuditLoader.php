<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Fixtures;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuditLoader
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function insertData($appCode, $eventDate, $eventCount, $eventType)
    {
        $sqlQuery = <<<SQL
INSERT INTO akeneo_app_audit (app_code, event_date, event_count, event_type)
VALUES (:app_code, :event_date, :event_count, :event_type)
SQL;
        $this->dbalConnection->executeQuery(
            $sqlQuery,
            ['app_code' => $appCode, 'event_date' => $eventDate, 'event_count' => $eventCount, 'event_type' => $eventType]
        );
    }
}
