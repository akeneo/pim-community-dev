<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Model;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantProductIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    public function testGetValuesForVariation()
    {
        $variantProduct = $this->getFromTestContainer('pim_catalog.repository.variant_product')
            ->findOneByIdentifier('1111111287');

        $this->getFromTestContainer('pim_catalog.updater.product')
            ->update($variantProduct, [
                'values' => [
                    'supplier' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'zaro'
                        ]
                    ]
                ]
            ]);

        $variationValues = $variantProduct->getValuesForVariation();

        $this->assertNull($variationValues->getByCodes('supplier'));
        $this->assertInstanceOf(ValueInterface::class, $variationValues->getByCodes('sku'));
    }
}
