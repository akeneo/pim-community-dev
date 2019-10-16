<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

abstract class AbstractExportTestCase extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->loadFixtures();
    }

    /**
     * Load the fixtures.
     */
    protected function loadFixtures() : void
    {
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
    protected function createProduct(string $identifier, array $data = []) : ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createVariantProduct(string $identifier, array $data = []) : ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    /**
     * @param array  $data
     *
     * @return ProductModelInterface
     */
    protected function createProductModel(array $data = []) : ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($productModel);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $productModel;
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function updateProduct(string $identifier, array $data = []) : ProductInterface
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    /**
     * @param array $data
     *
     * @return AttributeInterface
     */
    protected function createAttribute(array $data = []) : AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraintList = $this->get('validator')->validate($attribute);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    /**
     * @param array $data
     *
     * @return CategoryInterface
     */
    protected function createCategory(array $data = []) : CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    /**
     * @param array $data
     *
     * @return AttributeOptionInterface
     */
    protected function createAttributeOption(array $data = []) : AttributeOptionInterface
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $constraintList = $this->get('validator')->validate($attributeOption);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        return $attributeOption;
    }

    /**
     * @param array $data
     *
     * @return FamilyInterface
     */
    protected function createFamily(array $data = []) : FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param array $data
     *
     * @return FamilyVariantInterface
     */
    protected function createFamilyVariant(array $data = []) : FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    /**
     * @param string $expectedCsv
     * @param array  $config
     */
    protected function assertProductExport(string $expectedCsv, array $config) : void
    {
        $csv = $this->jobLauncher->launchExport('csv_product_export', null, $config);

        $this->assertEquals($expectedCsv, $csv);
    }

    /**
     * @param string $expectedCsv
     * @param array  $config
     */
    protected function assertProductModelExport(string $expectedCsv, array $config) : void
    {
        $csv = $this->jobLauncher->launchExport('csv_product_model_export', null, $config);

        $this->assertSame($expectedCsv, $csv);
    }
}
