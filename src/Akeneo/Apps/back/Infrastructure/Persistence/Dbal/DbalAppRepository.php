<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal;

use Akeneo\Apps\Domain\Model\App;
use Akeneo\Apps\Domain\Model\AppCode;
use Akeneo\Apps\Domain\Model\AppLabel;
use Akeneo\Apps\Domain\Model\FlowType;
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

    public function create(App $app): void
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
            $apps[] = new App(
                new AppCode($dataRow['code']),
                new AppLabel($dataRow['label']),
                new FlowType($dataRow['flow_type'])
            );
        }

        return $apps;
    }
}
