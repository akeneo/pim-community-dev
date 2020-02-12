<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\WrongCredentialsConnection\Persistence\Dbal;

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
SQL;

        $stmt = $this->dbalConnection->prepare($insertSQL);
        $stmt->execute([
            'connection_code' => $wrongCredentialsCombination->connectionCode(),
            'username' => $wrongCredentialsCombination->username(),
        ]);
    }

    public function findAll(\DateTime $since): array
    {
        $selectSql = <<<SQL
SELECT connection_code, username, MAX(authentication_date) as date
FROM akeneo_connectivity_connection_wrong_credentials_combination
WHERE authentication_date >= :since
GROUP BY connection_code, username
SQL;

        $results = $this->dbalConnection->executeQuery(
            $selectSql,
            ['since' => $since->format('Y-m-d')]
        )->fetchAll();

        return $this->normalize($results);
    }

    private function normalize(?array $wrongCombinations = []): array
    {
        $results = [];
        foreach ($wrongCombinations as $wrongCombination) {
            $code = $wrongCombination['connection_code'];
            if (!isset($results[$code])) {
                $results[$code] = [
                    'code' => $code,
                    'users' => [],
                ];
            }
            $results[$code]['users'][$wrongCombination['username']] = [
                'username' => $wrongCombination['username'],
                'date' => $wrongCombination['date'],
            ];
        }

        return $results;
    }
}
