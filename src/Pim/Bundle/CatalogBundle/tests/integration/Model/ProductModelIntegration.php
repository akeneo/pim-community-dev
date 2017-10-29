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
class ProductModelIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    public function testGetValuesForVariationForRootProductModel()
    {
        $rootProductModel = $this->getFromTestContainer('pim_catalog.repository.product_model')
            ->findOneByIdentifier('brooksblue');

        $this->getFromTestContainer('pim_catalog.updater.product_model')
            ->update($rootProductModel, [
                'values' => [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'testsku'
                        ]
                    ]
                ]
            ]);

        $variationValues = $rootProductModel->getValuesForVariation();

        $this->assertNull($variationValues->getByCodes('sku'));
        $this->assertInstanceOf(ValueInterface::class, $variationValues->getByCodes('variation_name', null, 'en_US'));
    }

    public function testGetValuesForVariationForSubProductModel()
    {
        $rootProductModel = $this->getFromTestContainer('pim_catalog.repository.product_model')
            ->findOneByIdentifier('model-running-shoes-m');

        $this->getFromTestContainer('pim_catalog.updater.product_model')
            ->update($rootProductModel, [
                'values' => [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'testsku'
                        ]
                    ],
                    'description' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'en_US',
                            'data' => 'A new description'
                        ]
                    ]
                ]
            ]);

        $variationValues = $rootProductModel->getValuesForVariation();

        $this->assertNull($variationValues->getByCodes('sku'));
        $this->assertNull($variationValues->getByCodes('description'));
        $this->assertInstanceOf(ValueInterface::class, $variationValues->getByCodes('variation_name', null, 'en_US'));
    }
}
