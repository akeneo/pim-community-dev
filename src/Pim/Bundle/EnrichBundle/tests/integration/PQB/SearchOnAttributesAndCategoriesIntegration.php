<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\tests\integration\PQB;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 *
 * Category tree:
 *
 * master
 * |
 * +-----> collection-2017
 * |               |
 * |               +------> winter-2017
 * +-----> collection-2018
 * |               |
 * |               +------> winter-2018
 * +-----> shoes
 *           |
 *           +-----> men
 *           +-----> women
 *
 *
 * Family Variant configuration:
 * | Common      | Level 1 | Level 2    |
 * | description | size    | color, sku |
 *
 * Dataset:
 *
 *                       +----+ model-shoe                   +----+ model-winter-2017
 *                       |    | category: "shoes"            |    | category: "winter-2017"
 *                       |    | description: "Superb shoe!"  |    | description: "2017 shoe!"
 *                       +----+ brand: null                  +----+
 *                      X  X                                  X
 *                     X    X                                X
 *                    X      X                              X
 *                   X        X                            X
 *             +----+         +----+                       X
 *     model-s |    |         |    | model-m               X
 *             |    |         |    | category:             X
 *             +----+         +----+   "collection-2018"   X
 *            X      X         X    X                      X
 *           X        X        X     X                    X
 *          X          X       X      X                  X
 *         X            X      X       X                X
 *        X              X     X        X              X
 *  +----+          +----+   +----+    +----+      +-----+        +-----+                           +------+
 *  |    |          |    |   |    |    |    |      |     |        |     |                           |      |
 *  |    |          |    |   |    |    |    |      |     |        |     |                           |      |
 *  +----+          +----+   +----+    +----+      +-----+        +-----+                           +------+
 *  red-s          blue-s     red-m     blue-m     blue-2017-m    another-shoe                       unclassified-product
 *  category:      category:  category: category:  category:      category: "women,winter-2018"      description: "quantum mechanics"
 *  "women"        "men"      "women"   "men"       ""            description: "Superb other shoe"
 *                                                                brand: "nyke"
 *
 * Tests of the filter category used in the UI along with some filters with attributes. The search results needs be
 * aggregated towards higher product model level as much as possible.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchOnAttributesAndCategoriesIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createCategoryTree();
        $this->createStructure();
        $this->createDataset();
    }

    /**
     * Given:
     *      - Select a category set on the root product model (without including sub categories)
     *      - Filter on attribute set on the root product model
     * should show:
     *      - The root product model
     */
    public function testWithoutIncludingSubCategoriesSelectCategoryAndAttributeOnRootProductModel(): void
    {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['shoes']],
            ['description', Operators::STARTS_WITH, 'Superb'],
        ]);
        $this->assert($result, ['model-shoe']);
    }

    /**
     * Given:
     *      - Select a category set on the root product model (including sub categories)
     *      - Filter on attribute set on the root product model
     * should show:
     *      - The root product model
     *      - Other products being in the category children and satisfying the attribute filter
     */
    public function testIncludingSubCategoriesSelectCategoryAndAttributeOnRootProductModel(): void
    {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['shoes', 'men', 'women']],
            ['description', Operators::STARTS_WITH, 'Superb'],
        ]);
        $this->assert($result, ['model-shoe', 'another-shoe']);
    }

    /**
     * Given:
     *      - Select a category set on a root product model (without including sub categories)
     *      - Filter on attribute set on the sub product model
     * should show:
     *      - The sub product model
     */
    public function testWithoutIncludingSubCategoriesSelectCategoryOnRootProductModelAndFilterOnAttributeInSubProductModel(): void
    {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['shoes']],
            ['size', Operators::IN_LIST, ['s']],
        ]);
        $this->assert($result, ['model-s']);
    }

    /**
     * Given:
     *      - Select a category set on a root product model (Without including sub categories)
     *      - Filter on attribute set on product-variants
     * should show:
     *      - The product variants satisfying the filter on attribute
     */
    public function testWithoutIncludingSubCategoriesSelectCategoryOnRootProductModelAndFilterOnAttributeInProductVariant()
    : void {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['shoes']],
            ['color', Operators::IN_LIST, ['blue']],
        ]);
        $this->assert($result, ['blue-s', 'blue-m']);
    }

    /**
     * Given:
     *      - Select a category set on a variant product
     *      - Filter on attribute set on product model
     * should show:
     *      - The product variants satisfying the attribute filter
     */
    public function testSelectCategoryOnVariantProductAndFilterOnAttributeSetInProductModel()
    : void {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['men']],
            ['description', Operators::STARTS_WITH, 'Superb'],
        ]);
        $this->assert($result, ['blue-s', 'blue-m']);
    }

    /**
     * Given:
     *      - Filter on attribute set on root product model
     *      - Select a category set on a sub product model (including sub categories)
     * should show:
     *      - The product and the sub product model
     */
    public function testIncludingSubCategoriesFilterAttributeSetOnRootProductModelAndSelectCategoryOnSubProductModel()
    : void {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['collection-2018', 'winter-2018']],
            ['description', Operators::STARTS_WITH, 'Superb'],
        ]);
        $this->assert($result, ['model-m', 'another-shoe']);
    }

    /**
     * Given:
     *      - Filter on attribute set on root product model
     *      - Select a category set on a variant product
     * should show:
     *      - The product variants having the category and inheriting the root product model values
     *      - Other products stasifying this filter.
     */
    public function testFilterAttributeSetOnRootProductModelAndSelectCategoryOnProductVariant(): void
    {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['women']],
            ['description', Operators::STARTS_WITH, 'Superb'],
        ]);
        $this->assert($result, ['red-s', 'red-m', 'another-shoe']);
    }

    /**
     * Select all unclassified and filter on an attribute
     */
    public function testShowAllUnclassifiedAndFilterOnAttribute(): void
    {
        $result = $this->executeFilter([
            ['categories', Operators::UNCLASSIFIED, []],
            ['description', Operators::EQUALS, 'quantum mechanics']
        ]);
        $this->assert($result, ['unclassified-product']);
    }

    /**
     * Using the IS_EMPTY operator should only show the products and product models which should have the selected
     * attribute (because it is in their family) and not the others.
     */
    public function testSelectIsEmptyOrNotEmptyOnAnAttribute(): void
    {
        sleep(5);
        $result = $this->executeFilter([
            ['brand', Operators::IS_EMPTY, '']
        ]);
        $this->assert($result, ['model-shoe', 'model-winter-2017']);

        $result = $this->executeFilter([
            ['brand', Operators::IS_NOT_EMPTY, '']
        ]);
        $this->assert($result, ['another-shoe']);
    }

    /**
     * Given:
     *      - Select a sub category set on the sub product model
     *      - Filter on root categorie
     * should show:
     *      - The root product model
     */
    public function testWithSubCategoriesSelectRootCategory(): void
    {
        $result = $this->executeFilter([
            ['categories', Operators::IN_LIST, ['collection-2017']]
        ]);
        $this->assert($result, ['model-winter-2017']);
    }

    /**
     * Creates the category tree.
     *
     * See the class PhpDoc for more info.
     */
    private function createCategoryTree(): void
    {
        $this->createCategory(['code' => 'shoes', 'parent' => 'master']);
        $this->createCategory(['code' => 'collection-2018', 'parent' => 'master']);
        $this->createCategory(['code' => 'winter-2018', 'parent' => 'collection-2018']);
        $this->createCategory(['code' => 'collection-2017', 'parent' => 'master']);
        $this->createCategory(['code' => 'winter-2017', 'parent' => 'collection-2017']);
        $this->createCategory(['code' => 'men', 'parent' => 'shoes']);
        $this->createCategory(['code' => 'women', 'parent' => 'shoes']);
    }

    /**
     * Creates all the products and product models.
     *
     * See the class PhpDoc for more info.
     */
    protected function createDataset(): void
    {
        $this->createProductModel([
            'code'           => 'model-shoe',
            'family_variant' => 'shoe_size_color',
            'parent'         => null,
            'categories'     => ['shoes'],
            'values'         => ['description' => [['data' => 'Superb shoe!', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createProductModel([
            'code'           => 'model-winter-2017',
            'family_variant' => 'shoe_size',
            'parent'         => null,
            'categories'     => ['winter-2017'],
            'values'         => ['description' => [['data' => '2017 winter shoe!', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createProductModel([
            'code'           => 'model-s',
            'family_variant' => 'shoe_size_color',
            'parent'         => 'model-shoe',
            'values'         => ['size' => [['data' => 's', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createProductModel([
            'code'           => 'model-m',
            'parent'         => 'model-shoe',
            'categories'     => ['collection-2018'],
            'family_variant' => 'shoe_size_color',
            'values'         => ['size' => [['data' => 'M', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createVariantProduct('red-s', [
            'parent'     => 'model-s',
            'categories' => ['women'],
            'values'     => ['color' => [['data' => 'red', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createVariantProduct('blue-s', [
            'parent'     => 'model-s',
            'categories' => ['men'],
            'values'     => ['color' => [['data' => 'blue', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createVariantProduct('red-m', [
            'parent'     => 'model-m',
            'categories' => ['women'],
            'values'     => ['color' => [['data' => 'red', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createVariantProduct('blue-m', [
            'parent'     => 'model-m',
            'categories' => ['men'],
            'values'     => ['color' => [['data' => 'blue', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createVariantProduct('blue-2017-m', [
            'parent'     => 'model-winter-2017',
            'categories' => [],
            'values'     => ['size' => [['data' => 'M', 'locale' => null, 'scope' => null]]],
        ]);
        $this->createProduct('another-shoe', [
            'categories' => ['women', 'winter-2018'],
            'values' => [
                'color'       => [['data' => 'blue', 'locale' => null, 'scope' => null]],
                'description' => [['data' => 'Superb other shoe!', 'locale' => null, 'scope' => null]],
                'brand' => [['data' => 'nyke', 'locale' => null, 'scope' => null]],
            ],
        ]);
        $this->createProduct('unclassified-product', [
            'values' => [
                'description' => [['data' => 'quantum mechanics', 'locale' => null, 'scope' => null]],
            ],
        ]);
        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();
        $this->get('akeneo_elasticsearch.client.product_model')->refreshIndex();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * Creates a catalog structure (family, family variants, attributes)
     */
    private function createStructure(): void
    {
        // Attributes
        $this->createAttribute([
            'code'        => 'description',
            'type'        => AttributeTypes::TEXT,
            'localizable' => false,
            'scopable'    => false,
        ]);

        // color
        $this->createAttribute([
            'code'        => 'color',
            'type'        => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createAttributeOption([
            'attribute' => 'color',
            'code'      => 'red'
        ]);
        $this->createAttributeOption([
            'attribute' => 'color',
            'code'      => 'blue'
        ]);

        // size
        $this->createAttribute([
            'code'        => 'size',
            'type'        => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createAttributeOption([
            'attribute' => 'size',
            'code'      => 's'
        ]);
        $this->createAttributeOption([
            'attribute' => 'size',
            'code'      => 'm'
        ]);

        $this->createAttribute([
            'code'        => 'brand',
            'type'        => AttributeTypes::TEXT,
            'localizable' => false,
            'scopable'    => false,
        ]);

        // Family & Family variant
        $this->createFamily([
            'code'                   => 'shoes',
            'attributes'             => ['sku', 'color', 'size', 'description', 'brand'],
            'attribute_requirements' => [],
        ]);

        $this->createFamilyVariant([
            'code'                   => 'shoe_size',
            'family'                 => 'shoes',
            'variant_attribute_sets' => [
                [
                    'level'      => 1,
                    'axes'       => ['size'],
                    'attributes' => ['size'],
                ]
            ],
        ]);
        $this->createFamilyVariant([
            'code'                   => 'shoe_size_color',
            'family'                 => 'shoes',
            'variant_attribute_sets' => [
                [
                    'level'      => 1,
                    'axes'       => ['size'],
                    'attributes' => ['size'],
                ],
                [
                    'level'      => 2,
                    'axes'       => ['color'],
                    'attributes' => ['color'],
                ],
            ],
        ]);
    }

    /**
     * @param array $data
     */
    private function createProductModel(array $data)
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            $data
        );

        $violations = $this->get('validator')->validate($productModel);
        $this->assertEquals(0, $violations->count());

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    /**
     * @param string $identifier
     * @param array  $data
     */
    protected function createVariantProduct(string $identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.product')->save($product);
    }

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function executeFilter(array $filters)
    {
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            ['limit' => 100]
        );

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $result
     * @param array           $expected
     */
    protected function assert(CursorInterface $result, array $expected)
    {
        $entities = [];
        foreach ($result as $entity) {
            if ($entity instanceof ProductInterface) {
                $entities[] = $entity->getIdentifier();
            } elseif ($entity instanceof ProductModelInterface) {
                $entities[] = $entity->getCode();
            }
        }

        sort($entities);
        sort($expected);

        $this->assertSame($expected, $entities);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
