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
final class VariantProductCompletenessRatio implements VariantProductRatioInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function findComplete(ProductModelInterface $productModel): CompleteVariantProducts
    {
        $completenesses = $this->getCompletenessesFor($productModel);
        $result = [];
        foreach ($completenesses as $productUuid => $completenessByChannel) {
            foreach ($completenessByChannel as $channelCode => $completenessByLocale) {
                foreach ($completenessByLocale as $localeCode => $value) {
                    $result[] = [
                        'product_uuid' => $productUuid,
                        'locale_code' => $localeCode,
                        'channel_code' => $channelCode,
                        'complete' => $value['missing'] === 0 ? 1 : 0
                    ];
                }
            }
        }

        return new CompleteVariantProducts($result);
    }

    private function getCompletenessesFor(ProductModelInterface $productModel): array
    {
        $sql = <<<SQL
WITH descendant_product_uuids as ( 
    SELECT uuid
        FROM pim_catalog_product product
        WHERE product.product_model_id = :product_model_id
    UNION ALL 
    SELECT uuid
        FROM pim_catalog_product product
        INNER JOIN pim_catalog_product_model product_model ON product_model.id = product.product_model_id
        WHERE product_model.parent_id = :product_model_id
)          
    SELECT BIN_TO_UUID(product_uuid) AS product_uuid, completeness 
    FROM pim_catalog_product_completeness completeness
    JOIN descendant_product_uuids ON descendant_product_uuids.uuid = completeness.product_uuid
SQL;

        return \array_map(
            static fn (string $json): array => \json_decode($json, true),
            $this->connection->fetchAllKeyValue($sql, [
                'product_model_id' => $productModel->getId(),
            ])
        );
    }
}
