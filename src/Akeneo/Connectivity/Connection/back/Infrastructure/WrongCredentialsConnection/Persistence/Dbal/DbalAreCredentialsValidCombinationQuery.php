<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\WrongCredentialsConnection\Persistence\Dbal;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalAreCredentialsValidCombinationQuery implements AreCredentialsValidCombinationQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $clientId, string $username): bool
    {
        $sqlQuery = <<<SQL
SELECT COUNT(connection.code)
FROM akeneo_connectivity_connection connection
INNER JOIN oro_user AS user ON connection.user_id = user.id
    AND connection.client_id = :client_id
    AND user.username = :username
SQL;
        $sqlParams = [
            'client_id' => $clientId,
            'username' => $username,
        ];

        return (bool) $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchColumn();
    }
}
