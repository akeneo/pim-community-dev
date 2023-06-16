<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionsQueryInterface;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsQuery implements SelectConnectionsQueryInterface
{
    public function __construct(private DbalConnection $dbalConnection)
    {
    }

    /**
     * @param string[] $types
     * @return Connection[]
     */
    public function execute(array $types = []): array
    {
        $parameters = [];
        $parametersTypes = [];

        $selectSQL = <<<SQL
SELECT code, label, flow_type, image, auditable, type
FROM akeneo_connectivity_connection
SQL;
        if ($types !== []) {
            $selectSQL .= <<<SQL
 WHERE type IN (:types)
SQL;
            $parameters['types'] = $types;
            $parametersTypes['types'] = DbalConnection::PARAM_STR_ARRAY;
        }

        $selectSQL .= <<<SQL
 ORDER BY created ASC
SQL;

        $dataRows = $this->dbalConnection
            ->executeQuery($selectSQL, $parameters, $parametersTypes)
            ->fetchAllAssociative();

        $connections = [];
        foreach ($dataRows as $dataRow) {
            $connections[] = new Connection(
                $dataRow['code'],
                $dataRow['label'],
                $dataRow['flow_type'],
                $dataRow['image'],
                (bool) $dataRow['auditable'],
                $dataRow['type'],
            );
        }

        return $connections;
    }
}
