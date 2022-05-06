<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindProductIdentifier implements FindIdentifier
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromId(int $id): null|string
    {
        $identifier = $this->connection->executeQuery(
            'SELECT identifier FROM pim_catalog_product WHERE id = :id',
            ['id' => $id]
        )->fetchOne();

        return false === $identifier ? null : $identifier;
    }
}
