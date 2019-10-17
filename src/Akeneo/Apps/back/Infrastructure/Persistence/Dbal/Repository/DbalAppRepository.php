<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Apps\Domain\Model\Read\App as ReadApp;
use Akeneo\Apps\Domain\Model\Write\App as WriteApp;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalAppRepository implements AppRepository
{
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function create(WriteApp $app): void
    {
        $insertSQL = <<<SQL
INSERT INTO akeneo_app (code, label, flow_type)
VALUES (:code, :label, :flow_type)
SQL;

        $stmt = $this->dbalConnection->prepare($insertSQL);
        $stmt->execute([
            'code' => (string) $app->code(),
            'label' => $app->label(),
            'flow_type' => (string) $app->flowType(),
        ]);
    }

    public function fetchAll(): array
    {
        $selectSQL = <<<SQL
SELECT code, label, flow_type FROM akeneo_app ORDER BY created ASC
SQL;

        $dataRows = $this->dbalConnection->executeQuery($selectSQL)->fetchAll();

        $apps = [];
        foreach ($dataRows as $dataRow) {
            $apps[] = new ReadApp($dataRow['code'], $dataRow['label'], $dataRow['flow_type']);
        }

        return $apps;
    }
}
