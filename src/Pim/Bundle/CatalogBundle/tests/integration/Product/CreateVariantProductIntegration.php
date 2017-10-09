<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Damien Carcel <damien.carcel@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateVariantProductIntegration extends TestCase
{
    public function testVariantProductHasParent(): void
    {
        $variantProduct = $this->get('pim_catalog.builder.variant_product')->createProduct('apollon_blue_m');
        $this->get('pim_catalog.updater.product')->update($variantProduct, [
            'family' => 'clothing',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'm',
                    ],
                ],
            ],
        ]);

        $errors = $this->get('validator')->validate($variantProduct);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'The variant product "apollon_blue_m" must have a parent',
            $errors->get(0)->getMessage()
        );
    }

    public function testVariantProductHasValidParent(): void
    {
        $variantProduct = $this->get('pim_catalog.builder.variant_product')->createProduct('minerva_blue_m');
        $this->get('pim_catalog.updater.product')->update($variantProduct, [
            'parent' => 'minerva',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'm',
                    ],
                ],
            ],
        ]);

        $errors = $this->get('validator')->validate($variantProduct);
        $this->assertEquals(2, $errors->count());
        $this->assertEquals(
            'The variant product "minerva_blue_m" cannot have product model "minerva" as parent, (this product model can only have other product models as children)',
            $errors->get(0)->getMessage()
        );
    }

    public function testVariantAxisValuesAreUnique(): void
    {
        $variantProduct1 = $this->get('pim_catalog.builder.variant_product')->createProduct('apollon_blue_m_1');
        $this->get('pim_catalog.updater.product')->update($variantProduct1, [
            'parent' => 'apollon_blue',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'm',
                    ],
                ],
            ],
        ]);
        $errors = $this->get('validator')->validate($variantProduct1);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.product')->save($variantProduct1);

        $variantProduct2 = $this->get('pim_catalog.builder.variant_product')->createProduct('apollon_blue_m_2');
        $this->get('pim_catalog.updater.product')->update($variantProduct2, [
            'parent' => 'apollon_blue',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'm',
                    ],
                ],
            ],
        ]);
        $errors = $this->get('validator')->validate($variantProduct2);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Cannot set value "[m]" for the attribute axis "size", as another sibling entity already has this value',
            $errors->get(0)->getMessage()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }
}
