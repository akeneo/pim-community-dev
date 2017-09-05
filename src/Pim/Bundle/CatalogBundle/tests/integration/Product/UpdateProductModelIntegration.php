<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductModelIntegration extends TestCase
{
    /**
     * TODO: This will become possible in PIM-6350.
     *
     * @expectedException \Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException
     * @expectedExceptionMessage Property "parent" cannot be modified, "amor" given.
     */
    public function testTheParentCannotBeChanged(): void
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon_blue');
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['parent' => 'amor',]);
    }

    /**
     * TODO: This will become possible in PIM-6344.
     *
     * @expectedException \Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException
     * @expectedExceptionMessage Property "family_variant" cannot be modified, "shoes_size" given.
     */
    public function testTheFamilyVariantCannotBeChanged(): void
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon_blue');
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['family_variant' => 'shoes_size',]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException
     * @expectedExceptionMessage Property "family_variant" cannot be modified, "shoes_size" given.
     */
    public function testTheFamilyVariantIsTheSameThanTheParent(): void
    {
        $productModel = $this->createProductModel(
            [
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
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.updater.product_model')->update($productModel, ['family_variant' => 'shoes_size',]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }

    /**
     * @param array $data
     *
     * @return ProductModelInterface
     */
    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        return $productModel;
    }
}
