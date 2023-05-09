<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Persistence;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalAreCredentialsValidCombinationQuery implements AreCredentialsValidCombinationQueryInterface
{
    public function __construct(private Connection $dbalConnection)
    {
    }

    public function execute(string $clientId, string $username): bool
    {
        $sqlQuery = <<<SQL
SELECT COUNT(c.code)
FROM akeneo_connectivity_connection as c
INNER JOIN oro_user AS u ON c.user_id = u.id
    AND c.client_id = :client_id
    AND u.username = :username
SQL;
        $sqlParams = [
            'client_id' => $clientId,
            'username' => $username,
        ];

        return (bool) $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchOne();
    }
}
