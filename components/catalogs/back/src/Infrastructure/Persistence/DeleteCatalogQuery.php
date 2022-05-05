<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\DeleteCatalogQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCatalogQuery implements DeleteCatalogQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $id): void
    {
        $this->connection->delete('akeneo_catalog', [
            'id' => Uuid::fromString($id)->getBytes(),
        ]);
    }
}
