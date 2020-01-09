<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectEventDatesToRefreshQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(): array
    {
        $selectSQL = <<<SQL
SELECT DISTINCT event_date FROM akeneo_connectivity_connection_audit 
WHERE event_date >= DATE(updated) ORDER BY event_date
SQL;
        return $this->dbalConnection->executeQuery($selectSQL)->fetchAll(FetchMode::COLUMN);
    }
}
