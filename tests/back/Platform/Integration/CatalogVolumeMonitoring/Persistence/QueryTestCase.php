<?php

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class QueryTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

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
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param array $data
     *
     * @return ProductInterface
     */
    protected function createProduct(array $data = []) : ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('new_product_'.rand());
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertEquals(0, $constraintList->count());

        $this->get('pim_catalog.saver.product')->save($product);

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
        Assert::assertEquals(0, $constraintList->count());
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
        Assert::assertEquals(0, $constraintList->count());
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
        Assert::assertEquals(0, $constraintList->count());
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
     * @param int $numberOfLevels
     * @return CategoryInterface
     */
    protected function createCategoryWithLevel(int $numberOfLevels) : CategoryInterface
    {
        $rootCategory = $this->createCategory([
            'code' => 'new_category_' . rand()
        ]);
        $this->get('validator')->validate($rootCategory);
        $this->get('pim_catalog.saver.category')->save($rootCategory);
        $previousLevelCode = $rootCategory->getCode();

        $i = 0;
        while ($i < $numberOfLevels) {
            $subCategory = $this->createCategory([
                'code' => 'new_category_' . rand(),
                'parent' => $previousLevelCode
            ]);
            $i++;
            $this->get('validator')->validate($subCategory);
            $this->get('pim_catalog.saver.category')->save($subCategory);
            $previousLevelCode = $subCategory->getCode();
        }

        return $rootCategory;
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
        Assert::assertEquals(0, $constraintList->count());
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
        Assert::assertEquals(0, $constraintList->count());
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
        Assert::assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    /**
     * @param int $numberOfAttributes
     */
    protected function createLocalizableAndScopableAttributes(int $numberOfAttributes)
    {
        $this->createAttributesWithLocalizableAndScopable($numberOfAttributes, true, true);
    }

    /**
     * @param int $numberOfAttributes
     */
    protected function createScopableAttributes(int $numberOfAttributes)
    {
        $this->createAttributesWithLocalizableAndScopable($numberOfAttributes, false, true);
    }

    /**
     * @param int $numberOfAttributes
     */
    protected function createLocalizableAttributes(int $numberOfAttributes)
    {
        $this->createAttributesWithLocalizableAndScopable($numberOfAttributes, true, false);
    }

    /**
     * @param int $numberOfAttributes
     * @param bool $localizable
     * @param bool $scopable
     */
    protected function createAttributesWithLocalizableAndScopable(int $numberOfAttributes, bool $localizable, bool $scopable): void
    {
        $i = 0;
        while ($i < $numberOfAttributes) {
            $this->createAttribute([
                    'code'     => 'new_attribute_' . rand(),
                    'type'     => 'pim_catalog_text',
                    'group'    => 'other',
                    'localizable' => $localizable,
                    'scopable' => $scopable
                ]);
            $i++;
        }
    }

    /**
     * @param int $numberOfProductValues
     */
    protected function createProductWithProductValues(int $numberOfProductValues): void
    {
        $family = $this->createFamily(['code' => 'new_family_' . rand()]);
        $arrayProductValues = [];
        $i = 0;

        // -1 because sku is automatically added
        while ($i < $numberOfProductValues -1) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_text',
                'group'    => 'other'
            ]);

            $family->addAttribute($attribute);
            $arrayProductValues[$attribute->getCode()] = [
                ['data' => rand().' some text random', 'locale' => null, 'scope' => null]
            ];
            $i++;
        }


        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);

        $this->createProduct([
            'identifier' => 'new_product_'.rand(),
            'family' => $family->getCode(),
            'values' => $arrayProductValues
        ]);
    }

    /**
     * @param int $numberOfProductValues
     */
    protected function createProductModelWithProductValues(int $numberOfProductValues): void
    {
        $axisAttribute = $this->createAttribute([
            'code'     => 'new_attribute_' . rand(),
            'type'     => 'pim_catalog_boolean',
            'group'    => 'other'
        ]);

        $family = $this->createFamily(['code' => 'new_family_' . rand()]);
        $family->addAttribute($axisAttribute);

        $productModelValues = [];
        $i = 0;
        while ($i < $numberOfProductValues) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_text',
                'group'    => 'other'
            ]);

            $family->addAttribute($attribute);
            $productModelValues[$attribute->getCode()] = [
                ['data' => rand().' some text random', 'locale' => null, 'scope' => null]
            ];
            $i++;
        }

        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);

        $familyVariant = $this->createFamilyVariant([
            'code'     => 'new_family_variant_' . rand(),
            'variant_attribute_sets' => [
                [
                    'axes' => [$axisAttribute->getCode()],
                    'attributes' => [],
                    'level'=> 1,
                ]
            ],
            'family' => $family->getCode()
        ]);

        $this->createProductModel([
            'code'           => 'new_product_model_' . rand(),
            'family_variant' => $familyVariant->getCode(),
            'values'         => $productModelValues
        ]);
    }
}
