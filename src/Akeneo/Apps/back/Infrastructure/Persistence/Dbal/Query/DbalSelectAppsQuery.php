<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppsQuery;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectAppsQuery implements SelectAppsQuery
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
SELECT code, label, flow_type FROM akeneo_app ORDER BY created ASC
SQL;

        $dataRows = $this->dbalConnection->executeQuery($selectSQL)->fetchAll();

        $apps = [];
        foreach ($dataRows as $dataRow) {
            $apps[] = new App($dataRow['code'], $dataRow['label'], $dataRow['flow_type']);
        }

        return $apps;
    }
}
