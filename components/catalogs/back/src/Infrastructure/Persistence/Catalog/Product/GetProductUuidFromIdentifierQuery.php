<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidFromIdentifierQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidFromIdentifierQuery implements GetProductUuidFromIdentifierQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(string $identifier): string
    {
        $sql = <<<SQL
            SELECT BIN_TO_UUID(uuid)
            FROM pim_catalog_product
            WHERE identifier = :identifier
        SQL;

        /** @var mixed|false $uuid */
        $uuid = $this->connection->fetchOne($sql, [
            'identifier' => $identifier,
        ]);

        if (false === $uuid) {
            throw new \InvalidArgumentException('Unknown identifier');
        }

        return (string) $uuid;
    }
}
