<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\ProductCompletenessRemoverInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Grégoire HUBERT <gregoire.hubert@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CompletenessRemover implements ProductCompletenessRemoverInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function deleteForOneProduct(UuidInterface $productUuid): int
    {
        return $this->deleteForProducts([$productUuid]);
    }

    public function deleteForProducts(array $productUuids): int
    {
        if ([] === $productUuids) {
            return 0;
        }

        $sql = <<<SQL
DELETE FROM pim_catalog_completeness AS pcc
WHERE pcc.product_uuid in (?)
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [\array_map(
                fn (UuidInterface $uuid): string => $uuid->getBytes(),
                $productUuids
            )],
            [Connection::PARAM_STR_ARRAY]
        );

        if (!method_exists($stmt, 'rowCount')) {
            throw new \RuntimeException('Cannot find the count of rows.');
        }

        return $stmt->rowCount();
    }
}
