<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Import;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ImportProductsWithAssociationsIntegration extends AbstractImportProductTestCase
{
    /** @test */
    public function i_can_associate_products_through_import_with_their_identifier()
    {
        $importFile = <<<CSV
uuid;sku;X_SELL-products;X_SELL-product_models
a7bcb820-e93f-4ecc-9c4d-69549278bd3a;sku2,sku3;pm1,pm2

CSV;
        $this->lauchImport($importFile);

        $this->assertAssociations(
            ['X_SELL' => ['products' => [self::UUID_SKU2, self::UUID_SKU3], 'product_models' => ['pm1', 'pm2']]],
            self::UUID_SKU1
        );
    }

    /** @test */
    public function i_can_associate_products_through_import_with_their_uuid()
    {
        $importFile = <<<CSV
uuid;sku;X_SELL-product_uuids;X_SELL-product_models
a7bcb820-e93f-4ecc-9c4d-69549278bd3a;b988bcfd-bb4d-4ddb-a2a2-d1cf926ab88c,105721c9-773b-441c-9a15-f2363d5187be;pm2,pm1

CSV;
        $this->lauchImport($importFile);

        $this->assertAssociations(
            ['X_SELL' => ['products' => [self::UUID_SKU2, self::UUID_EMPTY_IDENTIFIER], 'product_models' => ['pm2', 'pm1']]],
            self::UUID_SKU1
        );
    }

    private function assertAssociations(array $expectedAssociations, string $productUuid): void
    {
        /** @var ProductInterface $product */
        $product = $this->getProductRepository()->find($productUuid);
        foreach ($product->getAllAssociations() as $association) {
            $associationType = $association->getAssociationType()->getCode();
            Assert::assertSame(
                ($expectedAssociations[$associationType]['products'] ?? []),
                $product->getAssociatedProducts($associationType)->map(
                    static fn (ProductInterface $product): string => $product->getUuid()->toString()
                )->getValues()
            );
            Assert::assertSame(
                ($expectedAssociations[$associationType]['product_models'] ?? []),
                $product->getAssociatedProductModels($associationType)->map(
                    static fn (ProductModelInterface $model): string => $model->getCode()
                )->getValues()
            );
            Assert::assertSame(
                ($expectedAssociations[$associationType]['groups'] ?? []),
                $product->getAssociatedGroups($associationType)->map(
                    static fn (GroupInterface $group): string => $group->getCode()
                )->getValues()
            );
        }
    }
}
