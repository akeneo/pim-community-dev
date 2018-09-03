<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Doctrine\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\tests\fixture\EntityBuilder;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessGridFilterIntegration extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->getFromTestContainer('akeneo_integration_tests.catalog.fixture.completeness_filter')
            ->loadProductModelTree();

        sleep(5);
    }

    public function testThatItFindsCompleteFilterDataForARootProductModelWhichHaveTwoLevel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('root_product_model_two_level');

        $result = $this->get('pim_catalog.doctrine.query.complete_filter')
            ->findCompleteFilterData($productModel);

        $this->assertEquals(
            [
                'ecommerce' => [
                    'en_US' => 0,
                ],
                'ecommerce_china' => [
                    'en_US' => 0,
                    'zh_CN' => 0,
                ],
                'tablet' => [
                    'de_DE' => 1,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
            ],
            $result->allIncomplete(),
            'The number of variant product at all incomplete is wrong'
        );

        $this->assertEquals(
            [
                'ecommerce' => [
                    'en_US' => 1,
                ],
                'ecommerce_china' => [
                    'en_US' => 1,
                    'zh_CN' => 1,
                ],
                'tablet' => [
                    'de_DE' => 0,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
            ],
            $result->allComplete(),
            'The number of variant product all complete is wrong'
        );
    }

    public function testThatItFindsCompleteFilterDataForASubProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('sub_product_model');

        $result = $this->get('pim_catalog.doctrine.query.complete_filter')
            ->findCompleteFilterData($productModel);

        $this->assertEquals(
            [
                'ecommerce' => [
                    'en_US' => 0,
                ],
                'ecommerce_china' => [
                    'en_US' => 0,
                    'zh_CN' => 0,
                ],
                'tablet' => [
                    'de_DE' => 1,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
            ],
            $result->allIncomplete(),
            'The number of variant product all incomplete is wrong'
        );

        $this->assertEquals(
            [
                'ecommerce' => [
                    'en_US' => 1,
                ],
                'ecommerce_china' => [
                    'en_US' => 1,
                    'zh_CN' => 1,
                ],
                'tablet' => [
                    'de_DE' => 0,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
            ],
            $result->allComplete(),
            'The number of variant product all complete is wrong'
        );
    }

    public function testThatItFindsCompleteFilterDataForARootProductModelWhichHaveOneLevel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('root_product_model_one_level');

        $result = $this->get('pim_catalog.doctrine.query.complete_filter')
            ->findCompleteFilterData($productModel);

        $this->assertEquals(
            [
                'ecommerce' => [
                    'en_US' => 0,
                ],
                'ecommerce_china' => [
                    'en_US' => 0,
                    'zh_CN' => 0,
                ],
                'tablet' => [
                    'de_DE' => 1,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
            ],
            $result->allIncomplete(),
            'The number of variant product all incomplete is wrong'
        );

        $this->assertEquals(
            [
                'ecommerce' => [
                    'en_US' => 1,
                ],
                'ecommerce_china' => [
                    'en_US' => 1,
                    'zh_CN' => 1,
                ],
                'tablet' => [
                    'de_DE' => 0,
                    'en_US' => 0,
                    'fr_FR' => 1,
                ],
            ],
            $result->allComplete(),
            'The number of variant product all complete is wrong'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
