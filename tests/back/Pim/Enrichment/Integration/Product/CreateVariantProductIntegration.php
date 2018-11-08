<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Damien Carcel <damien.carcel@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateVariantProductIntegration extends TestCase
{
    public function testVariantProductHasValidParent(): void
    {
        $variantProduct = $this->get('pim_catalog.builder.product')->createProduct('minerva_blue_m');
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

        $errors = $this->get('pim_catalog.validator.product')->validate($variantProduct);
        $this->assertEquals(4, $errors->count());
        $this->assertEquals(
            'The variant product "minerva_blue_m" cannot have product model "minerva" as parent, (this product model can only have other product models as children)',
            $errors->get(1)->getMessage()
        );
    }

    public function testVariantAxisValuesCombinationIsUniqueInDatabase(): void
    {
        $variantProduct = $this->get('pim_catalog.builder.product')->createProduct('apollon_blue_m_bis');
        $this->get('pim_catalog.updater.product')->update($variantProduct, [
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

        $errors = $this->get('pim_catalog.validator.product')->validate($variantProduct);

        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Cannot set value "[m]" for the attribute axis "size" on variant product "apollon_blue_m_bis", as the variant product "1111111120" already has this value',
            $errors->get(0)->getMessage()
        );
    }

    public function testVariantAxisValuesCombinationIsUniqueInMemory(): void
    {
        $variantProduct1 = $this->get('pim_catalog.builder.product')->createProduct('apollon_blue_l_1');
        $this->get('pim_catalog.updater.product')->update($variantProduct1, [
            'parent' => 'apollon_blue',
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
        $errors = $this->get('pim_catalog.validator.product')->validate($variantProduct1);
        $this->assertEquals(0, $errors->count());

        $variantProduct2 = $this->get('pim_catalog.builder.product')->createProduct('apollon_blue_l_2');
        $this->get('pim_catalog.updater.product')->update($variantProduct2, [
            'parent' => 'apollon_blue',
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
        $errors = $this->get('pim_catalog.validator.product')->validate($variantProduct2);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'Cannot set value "[l]" for the attribute axis "size" on variant product "apollon_blue_l_2", as the variant product "apollon_blue_l_1" already has this value',
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
