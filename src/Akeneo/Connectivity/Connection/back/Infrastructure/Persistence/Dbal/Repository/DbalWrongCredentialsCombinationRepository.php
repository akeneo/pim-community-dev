<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombinations;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepository;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalWrongCredentialsCombinationRepository implements WrongCredentialsCombinationRepository
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function create(WrongCredentialsCombination $wrongCredentialsCombination): void
    {
        $insertSQL = <<<SQL
INSERT INTO akeneo_connectivity_connection_wrong_credentials_combination
VALUES (:connection_code, :username, NOW())
ON DUPLICATE KEY UPDATE authentication_date = NOW()
SQL;

        $stmt = $this->dbalConnection->prepare($insertSQL);
        $stmt->execute([
            'connection_code' => $wrongCredentialsCombination->connectionCode(),
            'username' => $wrongCredentialsCombination->username(),
        ]);
    }

    public function findAll(\DateTimeImmutable $since): WrongCredentialsCombinations
    {
        $selectSql = <<<SQL
SELECT connection_code, JSON_OBJECTAGG(username, authentication_date) as users
FROM akeneo_connectivity_connection_wrong_credentials_combination
WHERE authentication_date >= :since
GROUP BY connection_code
SQL;

        $results = $this->dbalConnection->executeQuery(
            $selectSql,
            ['since' => $since->format('Y-m-d')]
        )->fetchAll();

        if (null !== $results && !empty($results)) {
            array_walk($results, function (array &$combinations) {
                $combinations['users'] = json_decode($combinations['users'], true);
            });
        }

        return new WrongCredentialsCombinations($results);
    }
}
