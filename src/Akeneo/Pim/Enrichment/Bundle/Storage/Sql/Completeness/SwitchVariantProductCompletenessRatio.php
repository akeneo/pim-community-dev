<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SwitchVariantProductCompletenessRatio implements VariantProductRatioInterface
{
    private const TABLE_NAME = 'pim_catalog_product_completeness';

    public function __construct(
        private readonly VariantProductRatioInterface $legacyVariantProductRatio,
        private readonly VariantProductRatioInterface $variantProductRatio,
        private readonly Connection $connection,
    ) {
    }

    public function findComplete(ProductModelInterface $productModel): CompleteVariantProducts
    {
        if ($this->newTableExists()) {
            return $this->variantProductRatio->findComplete($productModel);
        }

        return $this->legacyVariantProductRatio->findComplete($productModel);
    }

    private function newTableExists(): bool
    {
        return $this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => self::TABLE_NAME,
            ]
        )->rowCount() >= 1;
    }
}
