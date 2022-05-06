<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateConnectedAppQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateConnectedAppQuery implements CreateConnectedAppQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(ConnectedApp $app): void
    {
        $insertQuery = <<<SQL
        INSERT INTO akeneo_connectivity_connected_app (id, name, logo, author, partner, categories, scopes, certified, connection_code, user_group_name)
        VALUES (:id, :name, :logo, :author, :partner, :categories, :scopes, :certified, :connection_code, :user_group_name)
        SQL;

        $this->connection->executeQuery(
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
                'user_group_name' => $app->getUserGroupName(),
            ],
            [
                'certified' => Types::BOOLEAN,
                'categories' => Types::JSON,
                'scopes' => Types::JSON,
            ]
        );
    }
}
