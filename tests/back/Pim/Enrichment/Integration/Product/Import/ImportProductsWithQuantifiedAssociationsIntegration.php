<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Import;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportProductsWithQuantifiedAssociationsIntegration extends AbstractImportProductTestCase
{
    /** @test */
    public function it_imports_quantified_associations_with_product_identifiers(): void
    {
        $csv = <<<CSV
        sku;quantified-products;quantified-products-quantity
        sku1;sku2,sku3;2|4
        CSV;
        $this->lauchImport($csv);

        $this->assertQuantifiedAssociations(
            self::UUID_SKU1,
            [
                'quantified' => [
                    'products' => [
                        [
                            'uuid' => self::UUID_SKU2,
                            'identifier' => 'sku2',
                            'quantity' => 2,
                        ],
                        [
                            'uuid' => self::UUID_SKU3,
                            'identifier' => 'sku3',
                            'quantity' => 4,
                        ],
                    ],
                    'product_models' => [],
                ],
            ]
        );
    }

    /** @test */
    public function it_imports_quantified_associations_with_product_uuids(): void
    {
        $csv = <<<CSV
        sku;quantified-product_uuids;quantified-products-quantity
        sku1;%s,%s,%s;2|4|3
        CSV;
        $this->lauchImport(\sprintf($csv, self::UUID_SKU2, self::UUID_EMPTY_IDENTIFIER, self::UUID_SKU3));

        $this->assertQuantifiedAssociations(
            self::UUID_SKU1,
            [
                'quantified' => [
                    'products' => [
                        [
                            'uuid' => self::UUID_SKU2,
                            'identifier' => 'sku2',
                            'quantity' => 2,
                        ],
                        [
                            'uuid' => self::UUID_SKU3,
                            'identifier' => 'sku3',
                            'quantity' => 3,
                        ],
                        [
                            'uuid' => self::UUID_EMPTY_IDENTIFIER,
                            'identifier' => null,
                            'quantity' => 4,
                        ],
                    ],
                    'product_models' => [],
                ],
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createQuantifiedAssociationType();
        $this->clearCache();
    }

    private function createQuantifiedAssociationType(): void
    {
        $associationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $associationType,
            [
                'code' => 'quantified',
                'is_quantified' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($associationType);
    }

    private function assertQuantifiedAssociations(string $uuid, array $expectedQuantifiedAssociations): void
    {
        $this->clearCache();
        $product = $this->getProductRepository()->find($uuid);
        Assert::assertInstanceOf(ProductInterface::class, $product);
        Assert::assertSame($expectedQuantifiedAssociations, $product->getQuantifiedAssociations()->normalize());
    }
}
