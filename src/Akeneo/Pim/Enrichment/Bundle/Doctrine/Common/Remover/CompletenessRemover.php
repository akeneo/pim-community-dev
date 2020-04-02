<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Remover;

use Doctrine\DBAL\Connection;

/**
 * @author    Grégoire HUBERT <gregoire.hubert@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessRemover
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * see deleteProducts() below
     */
    public function deleteOneProduct(int $productId): int
    {
        return $this->deleteProducts([$productId]);
    }

    /**
     * delete the elements from the completeness table
     * related to products passed as arguments
     * It returns the count of elements deleted.
     */
    public function deleteProducts(array $productIds): int
    {
        $sql = <<<SQL
DELETE FROM pim_catalog_completeness AS pcc
WHERE pcc.product_id in (?)
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [$productIds],
            [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );
        $count = $stmt->rowCount();

        return $count;
    }

    /**
     * delete the completeness elements related to products that do not exist
     * anymore. It returns the count of deleted rows.
     */
    public function cleanNonExistingProducts(): int
    {
        $sql = <<<SQL
DELETE FROM pim_catalog_completeness AS pcc
WHERE pcc.product_id NOT IN (SELECT id FROM pim_catalog_product AS pcp)
SQL;
        $stmt = $this->connection->executeQuery($sql);
        $count = $stmt->rowCount();

        return $count;
    }
}
