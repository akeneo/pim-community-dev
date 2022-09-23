<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\DisableCatalogsQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DisableCatalogsQuery implements DisableCatalogsQueryInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function execute(array $catalogIds): void
    {
        $query = <<<SQL
            UPDATE akeneo_catalog
            SET is_enabled = 0
            WHERE id IN (:uuids);
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'uuids' => \array_map(
                    static fn ($id) => Uuid::fromString($id)->getBytes(),
                    $catalogIds
                ),
            ],
            [
                'uuids' => Connection::PARAM_STR_ARRAY,
            ],
        );
    }
}
