<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindProductModelId implements FindId
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromIdentifier(string $identifier): null|string
    {
        $id = $this->connection->executeQuery(
            'SELECT id FROM pim_catalog_product_model WHERE code = :code',
            ['code' => $identifier]
        )->fetchOne();

        return false === $id ? null : (string)$id;
    }
}
