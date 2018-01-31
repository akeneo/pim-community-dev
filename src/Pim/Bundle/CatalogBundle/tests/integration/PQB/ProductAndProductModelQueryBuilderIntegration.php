<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB;

use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Test the ProductAndProductModelQueryBuilder can return both product and product models in a smart way.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductAndProductModelQueryBuilderIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    public function testNoFilterAndSortIdentifier()
    {
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            [
                'default_locale' => 'en_US',
                'default_scope'  => 'ecommerce',
                'limit'          => 19,
            ]
        );
        $pqb->addSorter('identifier', Directions::DESCENDING);

        $result = $pqb->execute();

        $this->assert(
            $result,
            [
                'watch',
                'zeus',
                'vulcanus',
                'venus',
                'terminus',
                'stock',
                'stilleto',
                'securitas',
                'quirinus',
                'poseidon',
                'portunus',
                'plain',
                'model-tshirt-unique-size',
                'model-tshirt-unique-color-kurt',
                'model-tshirt-divided',
                'model-running-shoes',
                'model-braided-hat',
                'model-biker-jacket',
                'moccasin',
            ]
        );
    }

    public function testIdentifierFilter()
    {
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            [
                'limit' => 19,
            ]
        );

        $pqb->addFilter(
            'identifier',
            Operators::IN_LIST,
            ['watch', 'model-tshirt-unique-size', 'tshirt-unique-size-crimson-red']
        );

        $result = $pqb->execute();

        $this->assert(
            $result,
            [
                'watch',
                'model-tshirt-unique-size',
                'tshirt-unique-size-crimson-red',
            ]
        );
    }

    public function testIdFilter()
    {
        $productId = IdEncoder::encode(
            IdEncoder::PRODUCT_TYPE,
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('watch')->getId()
        );
        $variantProductId = IdEncoder::encode(
            IdEncoder::PRODUCT_TYPE,
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('tshirt-unique-size-crimson-red')->getId()
        );
        $productModelId = IdEncoder::encode(
            IdEncoder::PRODUCT_MODEL_TYPE,
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-tshirt-unique-size')->getId()
        );

        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            [
                'limit' => 19,
            ]
        );

        $pqb->addFilter(
            'id',
            Operators::IN_LIST,
            [$productId, $variantProductId, $productModelId]
        );

        $result = $pqb->execute();

        $this->assert(
            $result,
            [
                'watch',
                'model-tshirt-unique-size',
                'tshirt-unique-size-crimson-red',
            ]
        );
    }

    public function testSearchTshirtInDescription()
    {
        $result = $this->executeFilter([['description', Operators::CONTAINS, 'Divided slim T-shirt with 2 buttons']]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided',
                'model-tshirt-unique-color-kurt',
                'model-tshirt-unique-size',
            ]
        );
    }

    /**
     * Simple search request that will return a mixed results of:
     * - VariantProducts (running-shoes-*)
     * - SubProductModel (model-tshirt-divided-crimson-red)
     * - RootProductModel (model-tshirt-unique-color)
     *
     * This mixed result is explained by the fact that the attribute "color" is not set at the same level within those 3
     * family variants.
     */
    public function testSearchColorRed()
    {
        $result = $this->executeFilter([['color', Operators::IN_LIST, ['crimson_red']]]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided-crimson-red',
                'model-tshirt-unique-color-kurt',
                'tshirt-unique-size-crimson-red',
                'running-shoes-xxs-crimson-red',
                'running-shoes-m-crimson-red',
                'running-shoes-xxxl-crimson-red',
            ]
        );
    }

    public function testSearchColorGrey()
    {
        $result = $this->executeFilter([['color', Operators::IN_LIST, ['battleship_grey']]]);

        $this->assert($result, ['model-tshirt-divided-battleship-grey', 'model-braided-hat']);
    }

    public function testSearchColorBlue()
    {
        $result = $this->executeFilter([['color', Operators::IN_LIST, ['navy_blue']]]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided-navy-blue',
                'tshirt-unique-size-navy-blue',
                'running-shoes-xxs-navy-blue',
                'running-shoes-m-navy-blue',
                'running-shoes-xxxl-navy-blue',
                'watch',
            ]
        );
    }

    public function testSearchSizeXXS()
    {
        $result = $this->executeFilter([['size', Operators::IN_LIST, ['xxs']]]);

        $this->assert(
            $result,
            [
                'tshirt-divided-battleship-grey-xxs',
                'tshirt-divided-navy-blue-xxs',
                'tshirt-divided-crimson-red-xxs',
                'tshirt-unique-color-kurt-xxs',
                'model-running-shoes-xxs',
                'biker-jacket-leather-xxs',
                'biker-jacket-polyester-xxs',
            ]
        );
    }

    public function testSearchSize3XL()
    {
        $result = $this->executeFilter([['size', Operators::IN_LIST, ['xxxl']]]);

        $this->assert(
            $result,
            [
                'tshirt-divided-battleship-grey-xxxl',
                'tshirt-divided-crimson-red-xxxl',
                'tshirt-divided-navy-blue-xxxl',
                'tshirt-unique-color-kurt-xxxl',
                'braided-hat-xxxl',
                'model-running-shoes-xxxl',
                'biker-jacket-leather-xxxl',
                'biker-jacket-polyester-xxxl',
            ]
        );
    }

    /**
     * Search request with 2 different attributes.
     *
     * Given those 2 attributes and a family variant (tree),
     * the search should return the documents which:
     * - level is the lowest between the levels set of those attributes
     * - and satisfy both conditions of the search.
     *
     * Ex: when searching for color=battleship-grey and size=s,
     *
     * We can see that the only products that satisfy those conditions are:
     * - The tshirt products and tshirt models with color battleship-grey and size s
     * - In the "clothing_color_size" family variant, size is defined at the product leve while color is defined at the
     *   subProductModel level. So we show the documents that belongs to the lowest involved. here level products.
     */
    public function testSearchColorGreyAndSizeXXS()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['battleship_grey']],
                ['size', Operators::IN_LIST, ['xxs']],
            ]
        );

        $this->assert($result, ['tshirt-divided-battleship-grey-xxs']);
    }

    public function testSearchColorGreyAndSize3XL()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['battleship_grey']],
                ['size', Operators::IN_LIST, ['xxxl']],
            ]
        );

        $this->assert($result, ['tshirt-divided-battleship-grey-xxxl', 'braided-hat-xxxl']);
    }

    public function testSearchColorGreyAndDescriptionTshirt()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['battleship_grey']],
                ['description', Operators::CONTAINS, 'T-shirt'],
            ]
        );

        $this->assert($result, ['model-tshirt-divided-battleship-grey']);
    }

    public function testSearchMaterialCotton()
    {
        $result = $this->executeFilter([['material', Operators::IN_LIST, ['cotton']]]);

        $this->assert(
            $result,
            [
                'model-tshirt-divided-battleship-grey',
                'model-tshirt-divided-crimson-red',
                'model-tshirt-unique-color-kurt',
                'model-tshirt-unique-size',
            ]
        );
    }

    public function testSearchMaterialLeather()
    {
        $result = $this->executeFilter([['material', Operators::IN_LIST, ['leather']]]);

        $this->assert(
            $result,
            [
                'model-running-shoes',
                'model-biker-jacket-leather',
            ]
        );
    }

    public function testSearchSize3XLColorWhite()
    {
        $result = $this->executeFilter(
            [
                ['size', Operators::IN_LIST, ['xxxl']],
                ['color', Operators::IN_LIST, ['antique_white']],
            ]
        );

        $this->assert(
            $result,
            ['running-shoes-xxxl-antique-white', 'biker-jacket-polyester-xxxl', 'biker-jacket-leather-xxxl']
        );
    }

    public function testRedCotton()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::IN_LIST, ['crimson_red']],
                ['material', Operators::IN_LIST, ['cotton']],
            ]
        );

        $this->assert(
            $result,
            [
                'model-tshirt-divided-crimson-red',
                'model-tshirt-unique-color-kurt',
                'tshirt-unique-size-crimson-red',
            ]
        );
    }

    public function testNotGreyAndXXS()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::NOT_IN_LIST, ['battleship_grey']],
                ['size', Operators::IN_LIST, ['xxs']],
            ]
        );

        $this->assert(
            $result,
            [
                'tshirt-divided-navy-blue-xxs',
                'tshirt-divided-crimson-red-xxs',
                'tshirt-unique-color-kurt-xxs',
                'running-shoes-xxs-antique-white',
                'running-shoes-xxs-navy-blue',
                'running-shoes-xxs-crimson-red',
                'biker-jacket-leather-xxs',
                'biker-jacket-polyester-xxs',
            ]
        );
    }

    public function testNotGreyAndNotXXSAndPolyester()
    {
        $result = $this->executeFilter(
            [
                ['color', Operators::NOT_IN_LIST, ['battleship_grey']],
                ['size', Operators::NOT_IN_LIST, ['xxs']],
                ['material', Operators::IN_LIST, ['polyester']],
            ]
        );

        $this->assert(
            $result,
            [
                'tshirt-divided-navy-blue-m',
                'tshirt-divided-navy-blue-l',
                'tshirt-divided-navy-blue-xxxl',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-xxxl',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->setParentsInTheDataset();
    }

    /**
     * TODO: remove this method once PIM-6335 has been merged
     * TODO: currently, the variant products can be imported (ie: we can not set parent to products yet)
     */
    private function setParentsInTheDataset()
    {
        $variantProductIdentifiers = [
            'tshirt-divided-navy-blue-xxs',
            'tshirt-divided-navy-blue-m',
            'tshirt-divided-navy-blue-l',
            'tshirt-divided-navy-blue-xxxl',
            'tshirt-divided-crimson-red-xxs',
            'tshirt-divided-crimson-red-m',
            'tshirt-divided-crimson-red-l',
            'tshirt-divided-crimson-red-xxxl',
            'tshirt-divided-battleship-grey-xxs',
            'tshirt-divided-battleship-grey-m',
            'tshirt-divided-battleship-grey-l',
            'tshirt-divided-battleship-grey-xxxl',
            'tshirt-unique-color-kurt-xxs',
            'tshirt-unique-color-kurt-m',
            'tshirt-unique-color-kurt-l',
            'tshirt-unique-color-kurt-xxxl',
            'braided-hat-m',
            'braided-hat-xxxl',
            'tshirt-unique-size-navy-blue',
            'tshirt-unique-size-crimson-red',
            'tshirt-unique-size-electric-yellow',
            'running-shoes-xxs-antique-white',
            'running-shoes-xxs-navy-blue',
            'running-shoes-xxs-crimson-red',
            'running-shoes-m-antique-white',
            'running-shoes-m-navy-blue',
            'running-shoes-m-crimson-red',
            'running-shoes-xxxl-antique-white',
            'running-shoes-xxxl-navy-blue',
            'running-shoes-xxxl-crimson-red',
            'biker-jacket-leather-xxs',
            'biker-jacket-leather-m',
            'biker-jacket-leather-xxxl',
            'biker-jacket-polyester-xxs',
            'biker-jacket-polyester-m',
            'biker-jacket-polyester-xxxl',
        ];

        // make the variant products really variants
        $dbal = $this->get('doctrine.dbal.default_connection');
        $dbal->executeUpdate(
            'UPDATE pim_catalog_product SET product_type = ? WHERE identifier IN (?)',
            ['variant_product', $variantProductIdentifiers],
            [\PDO::PARAM_STR, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        );

        $productRepository = $this->get('pim_catalog.repository.product');
        $productSaver = $this->get('pim_catalog.saver.product');
        $productModelRepository = $this->get('pim_catalog.repository.product_model');

        // detach previously loaded products
        // they are known as Product instead of VariantProduct by the UoW
        $em = $this->get('doctrine.orm.default_entity_manager');
        foreach ($variantProductIdentifiers as $variantProductIdentifier) {
            $variantProduct = $productRepository->findOneByIdentifier($variantProductIdentifier);
            $em->detach($variantProduct);
        }

        $identifiers = [
            'tshirt-divided-navy-blue-xxs',
            'tshirt-divided-navy-blue-m',
            'tshirt-divided-navy-blue-l',
            'tshirt-divided-navy-blue-xxxl',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-tshirt-divided-navy-blue');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'tshirt-divided-crimson-red-xxs',
            'tshirt-divided-crimson-red-m',
            'tshirt-divided-crimson-red-l',
            'tshirt-divided-crimson-red-xxxl',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-tshirt-divided-crimson-red');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'tshirt-divided-battleship-grey-xxs',
            'tshirt-divided-battleship-grey-m',
            'tshirt-divided-battleship-grey-l',
            'tshirt-divided-battleship-grey-xxxl',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-tshirt-divided-battleship-grey');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'tshirt-unique-color-kurt-xxs',
            'tshirt-unique-color-kurt-m',
            'tshirt-unique-color-kurt-l',
            'tshirt-unique-color-kurt-xxxl',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-tshirt-unique-color-kurt');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'braided-hat-m',
            'braided-hat-xxxl',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-braided-hat');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'tshirt-unique-size-navy-blue',
            'tshirt-unique-size-crimson-red',
            'tshirt-unique-size-electric-yellow',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-tshirt-unique-size');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'running-shoes-xxs-antique-white',
            'running-shoes-xxs-navy-blue',
            'running-shoes-xxs-crimson-red',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-running-shoes-xxs');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'running-shoes-m-antique-white',
            'running-shoes-m-navy-blue',
            'running-shoes-m-crimson-red',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-running-shoes-m');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'running-shoes-xxxl-antique-white',
            'running-shoes-xxxl-navy-blue',
            'running-shoes-xxxl-crimson-red',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-running-shoes-xxxl');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'biker-jacket-leather-xxs',
            'biker-jacket-leather-m',
            'biker-jacket-leather-xxxl',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-biker-jacket-leather');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $identifiers = [
            'biker-jacket-polyester-xxs',
            'biker-jacket-polyester-m',
            'biker-jacket-polyester-xxxl',
        ];
        $productModel = $productModelRepository->findOneByIdentifier('model-biker-jacket-polyester');
        foreach ($identifiers as $identifier) {
            $product = $productRepository->findOneByIdentifier($identifier);
            $product->setParent($productModel);
            $productSaver->save($product);
        }

        $this->indexProductModels();
        $this->indexProducts();
    }

    private function indexProducts()
    {
        $products = $this->get('pim_catalog.repository.product')->findAll();
        $this->get('pim_catalog.elasticsearch.indexer.product')->indexAll($products);
    }

    private function indexProductModels()
    {
        $productModels = $this->get('pim_catalog.repository.product_model')->findAll();
        $this->get('pim_catalog.elasticsearch.indexer.product_model')->indexAll($productModels);
    }
}
