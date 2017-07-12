<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelIntegration extends TestCase
{
    /**
     * Create a product without any errors
     */
    public function testTheProductModelCreation()
    {
        $productModel = $this->createProductModelObject(
            [
                'identifier' => 'product_model_identifier',
                'values' => [
                    'name' => [
                        [
                            'locale' => 'fr_FR',
                            'scope' => null,
                            'data' => 'T-shirt super beau',
                        ],
                    ],
                ],
                'categories' => ['tshirts'],
            ]
        );

        $errors = $this->get('validator')->validate($productModel);
        $this->assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->get('doctrine.orm.entity_manager')
            ->detach($productModel);

        /** @var ProductModelInterface $productModel */
        $productModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('product_model_identifier');

        $this->assertNotNull(
            $productModel,
            'The product model with the identifier "product_model_identifier" does not exist'
        );

        $this->assertEquals($productModel->getCategoryCodes(), ['tshirts']);

        $sku = $productModel->getValues()->first();
        $this->assertEquals($sku->getLocale(), 'fr_FR');
        $this->assertEquals($sku->getScope(), null);
        $this->assertEquals($sku->getData(), 'T-shirt super beau');
    }

//    /**
//     * Basic validation, a product model identifier must not be empty
//     */
//    public function testThatTheProductModelIdentifierMustNotBeEmpty()
//    {
//        $productModel = $this->createProductModelObject(
//            [
//
//            ]
//        );
//
//        $errors = $this->get('validator')->validate($productModel);
//
//        $this->assertEquals('The product model identifier must not be empty', $errors->get('TODO')->getMessage());
//    }
//
//    /**
//     * Basic validation, a product model identifier must be valide
//     */
//    public function testThatTheProductModelIdentifierMustBeValid()
//    {
//        $productModel = $this->createProductModelObject(
//            [
//
//            ]
//        );
//
//        $errors = $this->get('validator')->validate($productModel);
//
//        $this->assertEquals('The product model identifier must not be empty', $errors->get('TODO')->getMessage());
//    }
//
//    /**
//     * Family variant validation: A product model must all have the family attributes and its axes.
//     */
//    public function testTheProductModelValidityDependingOnItsFamily()
//    {
//        $productModel = $this->createProductModelObject(
//            [
//
//            ]
//        );
//
//        $errors = $this->get('validator')->validate($productModel);
//
//        $this->assertEquals(
//            'It misses the following attributes "%s" to the product model',
//            $errors->get('TODO')->getMessage()
//        );
//        $this->assertEquals(
//            'The value of following attributes "%s" must not be empty',
//            $errors->get('TODO')->getMessage()
//        );
//    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getFunctionalCatalog('catalog_modeling')]);
    }

    /**
     * @param array $data
     *
     * @return ProductModelInterface
     */
    private function createProductModelObject(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        return $productModel;
    }
}