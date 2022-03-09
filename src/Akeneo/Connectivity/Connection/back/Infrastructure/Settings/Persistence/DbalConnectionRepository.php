<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalConnectionRepository implements ConnectionRepositoryInterface
{
    public function __construct(private DbalConnection $dbalConnection)
    {
    }

    public function create(Connection $connection): void
    {
        $insertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection (client_id, user_id, code, label, flow_type, auditable, type)
VALUES (:client_id, :user_id, :code, :label, :flow_type, :auditable, :type)
SQL;

        $this->dbalConnection->executeQuery(
            $insertQuery,
            [
                'client_id' => $connection->clientId()->id(),
                'user_id' => $connection->userId()->id(),
                'code' => (string) $connection->code(),
                'label' => (string) $connection->label(),
                'flow_type' => (string) $connection->flowType(),
                'auditable' => $connection->auditable(),
                'type' => (string) $connection->type(),
            ],
            [
                'auditable' => Types::BOOLEAN,
            ]
        );
    }

    public function findOneByCode(string $code): ?Connection
    {
        $selectQuery = <<<SQL
SELECT code, label, flow_type, image, client_id, user_id, auditable, type
FROM akeneo_connectivity_connection
WHERE code = :code
SQL;

        $dataRow = $this->dbalConnection->executeQuery($selectQuery, ['code' => $code])->fetchAssociative();

        return $dataRow ?
            new Connection(
                $dataRow['code'],
                $dataRow['label'],
                $dataRow['flow_type'],
                (int) $dataRow['client_id'],
                (int) $dataRow['user_id'],
                $dataRow['image'],
                (bool) $dataRow['auditable'],
                $dataRow['type']
            ) : null;
    }

    public function update(Connection $connection): void
    {
        $updateQuery = <<<SQL
UPDATE akeneo_connectivity_connection
SET label = :label, flow_type = :flow_type, image = :image, auditable = :auditable
WHERE code = :code
SQL;

        $this->dbalConnection->executeQuery(
            $updateQuery,
            [
                'code' => (string) $connection->code(),
                'label' => (string) $connection->label(),
                'flow_type' => (string) $connection->flowType(),
                'image' => null !== $connection->image() ? (string) $connection->image() : null,
                'auditable' => (bool) $connection->auditable(),
            ],
            [
                'auditable' => Types::BOOLEAN,
            ]
        );
    }

    public function delete(Connection $connection): void
    {
        $deleteQuery = <<<SQL
DELETE FROM akeneo_connectivity_connection
WHERE code = :code
SQL;

        $this->dbalConnection->executeQuery(
            $deleteQuery,
            [
                'code' => (string) $connection->code(),
            ]
        );
    }
}
