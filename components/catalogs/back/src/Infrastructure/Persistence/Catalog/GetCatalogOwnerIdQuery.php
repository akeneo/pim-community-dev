<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogOwnerIdQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogOwnerIdQuery implements GetCatalogOwnerIdQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $catalogId): int
    {
        $query = <<<SQL
        SELECT catalog.owner_id
        FROM akeneo_catalog catalog
        WHERE catalog.id = :id
        SQL;

        /** @var mixed|false $userId */
        $userId = $this->connection->fetchOne($query, [
            'id' => Uuid::fromString($catalogId)->getBytes(),
        ]);

        if (null === $userId) {
            throw new \LogicException('Catalog not found');
        }

        return (int) $userId;
    }
}
