<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectedAppLoader
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @param string[] $categories
     * @param string[] $scopes
     */
    public function createConnectedApp(
        string $id,
        string $name,
        array $scopes,
        string $connectionCode,
        string $logo,
        string $author,
        array $categories,
        bool $certified,
        ?string $partner
    ): int {
        $query = <<<SQL
INSERT INTO akeneo_connectivity_app(id, name, logo, author, partner, categories, certified, connection_code, scopes)
VALUES (:id, :name, :logo, :author, :partner, :categories, :certified, :connection_code, :scopes)
SQL;

        return $this->dbalConnection->executeUpdate(
            $query,
            [
                'id' => $id,
                'name' => $name,
                'logo' => $logo,
                'author' => $author,
                'partner' => $partner,
                'categories' => $categories,
                'certified' => $certified,
                'connection_code' => $connectionCode,
                'scopes' => $scopes,
            ],
            [
                'certified' => Types::BOOLEAN,
                'categories' => Types::JSON,
                'scopes' => Types::JSON,
            ]
        );
    }
}
