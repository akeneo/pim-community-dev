<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalConnectedAppRepository implements ConnectedAppRepositoryInterface
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @return ConnectedApp[]
     */
    public function findAll(): array
    {
        $selectSQL = <<<SQL
SELECT id, name, scopes, connection_code, logo, author, categories, certified, partner, external_url
FROM akeneo_connectivity_app
ORDER BY name ASC
SQL;

        $dataRows = $this->dbalConnection->executeQuery($selectSQL)->fetchAll();

        $connectedApps = [];
        foreach ($dataRows as $dataRow) {
            $connectedApps[] = new ConnectedApp(
                $dataRow['id'],
                $dataRow['name'],
                \json_decode($dataRow['scopes'], true),
                $dataRow['connection_code'],
                $dataRow['logo'],
                $dataRow['author'],
                \json_decode($dataRow['categories'], true),
                (bool) $dataRow['certified'],
                $dataRow['partner'],
                $dataRow['external_url']
            );
        }

        return $connectedApps;
    }
}
