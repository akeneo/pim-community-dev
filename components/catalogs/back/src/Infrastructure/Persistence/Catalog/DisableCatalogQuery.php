<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DisableCatalogQuery implements DisableCatalogQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(string $catalogId): void
    {
        $query = <<<SQL
            UPDATE akeneo_catalog
            SET is_enabled = 0
            WHERE id = :catalogId;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'catalogId' => Uuid::fromString($catalogId)->getBytes(),
            ],
        );
    }
}
