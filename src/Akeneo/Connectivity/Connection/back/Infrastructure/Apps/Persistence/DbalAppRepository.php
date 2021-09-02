<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\App;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\AppRepositoryInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalAppRepository implements AppRepositoryInterface
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function create(App $app): void
    {
        $insertQuery = <<<SQL
        INSERT INTO akeneo_connectivity_app (id, name, logo, author, partner, categories, scopes, certified, connection_code, external_url)
        VALUES (:id, :name, :logo, :author, :partner, :categories, :scopes, :certified, :connection_code, :external_url)
        SQL;

        $this->dbalConnection->executeQuery(
            $insertQuery,
            [
                'id' => $app->getId(),
                'name' => $app->getName(),
                'logo' => $app->getLogo(),
                'author' => $app->getAuthor(),
                'partner' => $app->getPartner(),
                'categories' => $app->getCategories(),
                'scopes' => $app->getScopes(),
                'certified' => $app->isCertified(),
                'connection_code' => $app->getConnectionCode(),
                'external_url' => $app->getExternalUrl(),
            ],
            [
                'certified' => Types::BOOLEAN,
                'categories' => Types::JSON,
                'scopes' => Types::JSON,
            ]
        );
    }

    public function findOneById(string $appId): ?App
    {
        $selectQuery = <<<SQL
        SELECT id, name, logo, author, partner,categories, scopes, certified, connection_code, external_url
        FROM akeneo_connectivity_app
        WHERE id = :id
        SQL;

        $dataRow = $this->dbalConnection->executeQuery($selectQuery, ['id' => $appId])->fetch();

        return $dataRow ?
            new App(
                $dataRow['id'],
                $dataRow['name'],
                json_decode($dataRow['scopes'], true),
                $dataRow['connection_code'],
                $dataRow['logo'],
                $dataRow['author'],
                json_decode($dataRow['categories'], true),
                (bool) $dataRow['certified'],
                $dataRow['partner'],
                $dataRow['external_url']
            ) : null;
    }
}
