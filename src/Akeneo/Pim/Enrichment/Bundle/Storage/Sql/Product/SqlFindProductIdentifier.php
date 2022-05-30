<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindProductIdentifier implements FindIdentifier
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromUuid(string $uuid): null|string
    {
        $identifier = $this->connection->executeQuery(
            'SELECT identifier FROM pim_catalog_product WHERE uuid = :uuid',
            ['uuid' => Uuid::fromString($uuid)->getBytes()]
        )->fetchOne();

        return false === $identifier ? null : $identifier;
    }
}
