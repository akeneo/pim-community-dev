<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalAppRepository implements AppRepository
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function create(App $app): void
    {
        $insertSQL = <<<SQL
INSERT INTO akeneo_app (client_id, user_id, code, label, flow_type)
VALUES (:client_id, :user_id, :code, :label, :flow_type)
SQL;

        $stmt = $this->dbalConnection->prepare($insertSQL);
        $stmt->execute([
            'client_id' => $app->clientId()->id(),
            'user_id' => $app->userId()->id(),
            'code' => (string) $app->code(),
            'label' => (string) $app->label(),
            'flow_type' => (string) $app->flowType(),
        ]);
    }

    public function findOneByCode(string $code): ?App
    {
        $selectQuery = <<<SQL
SELECT code, label, flow_type, client_id, user_id
FROM akeneo_app
WHERE code = :code
SQL;

        $dataRow = $this->dbalConnection->executeQuery($selectQuery, ['code' => $code])->fetch();

        return $dataRow ?
            new App(
                $dataRow['code'],
                $dataRow['label'],
                $dataRow['flow_type'],
                new ClientId((int) $dataRow['client_id']),
                new UserId((int) $dataRow['user_id'])
            ) : null;
    }

    public function update(App $app): void
    {
        $updateQuery = <<<SQL
UPDATE akeneo_app
SET label = :label, flow_type = :flow_type
WHERE code = :code
SQL;

        $stmt = $this->dbalConnection->prepare($updateQuery);
        $stmt->execute([
            'code' => (string) $app->code(),
            'label' => (string) $app->label(),
            'flow_type' => (string) $app->flowType(),
        ]);
    }
}
