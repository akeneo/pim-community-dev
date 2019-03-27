<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductModelIntegration extends TestCase
{
    /**
     * TODO: This will become possible in PIM-6344.
     */
    public function testTheFamilyVariantCannotBeChanged(): void
    {
        $this->expectException(ImmutablePropertyException::class);
        $this->expectExceptionMessage('Property "family_variant" cannot be modified, "shoes_size" given.');

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon_blue');
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['family_variant' => 'shoes_size',]);
    }

    public function testTheFamilyVariantIsTheSameThanTheParent(): void
    {
        $this->expectException(ImmutablePropertyException::class);
        $this->expectExceptionMessage('Property "family_variant" cannot be modified, "shoes_size" given.');

        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'code' => 'model-running-shoes-l',
            'parent' => 'model-running-shoes',
            'family_variant' => 'shoes_size_color',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'l',
                    ],
                ],
            ],
        ]);

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.updater.product_model')->update($productModel, ['family_variant' => 'shoes_size',]);
    }

    public function testTheVariantAxisValuesCannotBeUpdated(): void
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon_blue');
        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'values' => [
                'color' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'black',
                    ],
                ],
            ],
        ]);

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Variant axis "color" cannot be modified, "[black]" given',
            $errors->get(0)->getMessage()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
