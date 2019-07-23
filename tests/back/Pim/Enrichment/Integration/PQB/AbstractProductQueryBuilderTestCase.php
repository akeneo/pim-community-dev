<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductQueryBuilderTestCase extends TestCase
{
    /** @var Client */
    protected $esProductClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct($identifier, array $data)
    {
        $family = isset($data['family']) ? $data['family'] : null;

        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $family);
        $this->updateProduct($product, $data);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $data
     */
    protected function updateProduct(ProductInterface $product, array $data)
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->esProductClient->refreshIndex();
    }

    /**
     * @param array $data
     */
    protected function createAttribute(array $data)
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        $this->assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param array $data
     */
    protected function createAttributeOption(array $data)
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    /**
     * @param array $data
     */
    protected function createFamily(array $data)
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraints = $this->get('validator')->validate($family);
        $this->assertCount(0, $constraints);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    protected function createFamilyVariant(array $data = []) : FamilyVariantInterface
    {
        $family_variant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family_variant, $data);
        $constraintList = $this->get('validator')->validate($family_variant);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family_variant);

        return $family_variant;
    }

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function executeFilter(array $filters)
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory')->create();

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param array $sorters
     * @param array $options
     *
     * @return CursorInterface
     */
    protected function executeSorter(array $sorters, $options = [])
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory')->create($options);

        foreach ($sorters as $sorter) {
            $context = isset($sorter[2]) ? $sorter[2] : [];
            $pqb->addSorter($sorter[0], $sorter[1], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $result
     * @param array           $expected
     */
    protected function assert(CursorInterface $result, array $expected)
    {
        $products = [];
        foreach ($result as $product) {
            $products[] = $product->getIdentifier();
        }

        sort($products);
        sort($expected);

        $this->assertSame($products, $expected);
    }

    /**
     * @param CursorInterface $result
     * @param array $expected
     */
    protected function assertOrder(CursorInterface $result, array $expected)
    {
        $products = [];
        foreach ($result as $product) {
            $products[] = $product->getIdentifier();
        }

        $this->assertSame($products, $expected);
    }
}
