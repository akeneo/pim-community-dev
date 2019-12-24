<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Domain\Settings\Model\Read\Connection;
use Akeneo\Apps\Domain\Settings\Persistence\Query\SelectConnectionsQuery;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsQuery implements SelectConnectionsQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(): array
    {
        $selectSQL = <<<SQL
SELECT code, label, flow_type, image FROM akeneo_app ORDER BY created ASC
SQL;

        $dataRows = $this->dbalConnection->executeQuery($selectSQL)->fetchAll();

        $connections = [];
        foreach ($dataRows as $dataRow) {
            $connections[] = new Connection($dataRow['code'], $dataRow['label'], $dataRow['flow_type'], $dataRow['image']);
        }

        return $connections;
    }
}
