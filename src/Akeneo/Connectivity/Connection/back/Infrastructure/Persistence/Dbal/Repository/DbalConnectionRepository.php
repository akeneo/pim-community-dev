<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalConnectionRepository implements ConnectionRepository
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function create(Connection $connection): void
    {
        $insertSQL = <<<SQL
INSERT INTO akeneo_connectivity_connection (client_id, user_id, code, label, flow_type)
VALUES (:client_id, :user_id, :code, :label, :flow_type)
SQL;

        $stmt = $this->dbalConnection->prepare($insertSQL);
        $stmt->execute([
            'client_id' => $connection->clientId()->id(),
            'user_id' => $connection->userId()->id(),
            'code' => (string) $connection->code(),
            'label' => (string) $connection->label(),
            'flow_type' => (string) $connection->flowType(),
        ]);
    }

    public function findOneByCode(string $code): ?Connection
    {
        $selectQuery = <<<SQL
SELECT code, label, flow_type, image, client_id, user_id
FROM akeneo_connectivity_connection
WHERE code = :code
SQL;

        $dataRow = $this->dbalConnection->executeQuery($selectQuery, ['code' => $code])->fetch();

        return $dataRow ?
            new Connection(
                $dataRow['code'],
                $dataRow['label'],
                $dataRow['flow_type'],
                (int) $dataRow['client_id'],
                new UserId((int) $dataRow['user_id']),
                $dataRow['image']
            ) : null;
    }

    public function update(Connection $connection): void
    {
        $updateQuery = <<<SQL
UPDATE akeneo_connectivity_connection
SET label = :label, flow_type = :flow_type, image = :image
WHERE code = :code
SQL;
        $params = [
            'code' => (string) $connection->code(),
            'label' => (string) $connection->label(),
            'flow_type' => (string) $connection->flowType(),
            'image' => null !== $connection->image() ? (string) $connection->image() : null,
        ];

        $stmt = $this->dbalConnection->prepare($updateQuery);
        $stmt->execute($params);
    }

    public function delete(Connection $connection): void
    {
        $deleteQuery = <<<SQL
DELETE FROM akeneo_connectivity_connection
WHERE code = :code
SQL;

        $stmt = $this->dbalConnection->prepare($deleteQuery);
        $stmt->execute([
            'code' => (string) $connection->code(),
        ]);
    }
}
