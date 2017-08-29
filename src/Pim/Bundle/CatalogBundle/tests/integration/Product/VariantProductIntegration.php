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
class VariantProductIntegration extends TestCase
{
    public function testVariantProductHasParent(): void
    {
        $variantProduct = $this->get('pim_catalog.builder.variant_product')->createProduct('apollon_blue_m');
        $this->get('pim_catalog.updater.product')->update($variantProduct, [
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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }
}
