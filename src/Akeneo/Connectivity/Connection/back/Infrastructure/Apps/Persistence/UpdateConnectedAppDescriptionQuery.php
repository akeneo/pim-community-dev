<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppDescriptionQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateConnectedAppDescriptionQuery implements UpdateConnectedAppDescriptionQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(ConnectedApp $app): void
    {
        $query = <<<SQL
        UPDATE akeneo_connectivity_connected_app
        SET
            name = :name,
            logo = :logo,
            author = :author,
            categories = :categories,
            certified = :certified,
            partner = :partner,
            updated = NOW()
        WHERE id = :id
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => $app->getId(),
                'name' => $app->getName(),
                'logo' => $app->getLogo(),
                'author' => $app->getAuthor(),
                'categories' => $app->getCategories(),
                'certified' => $app->isCertified(),
                'partner' => $app->getPartner(),
            ],
            [
                'categories' => Types::JSON,
                'certified' => Types::BOOLEAN,
            ]
        );
    }
}
