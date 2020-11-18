<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\ConstraintViolationList;

abstract class WithRemovedAttributeTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function loadFixtures(): void
    {
        $this->createAttribute([
            'code' => 'an_attribute',
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->createAttribute([
            'code' => 'a_second_attribute',
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->createAttribute([
            'code' => 'a_third_attribute',
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->createAttribute([
            'code' => 'a_forth_attribute',
            'type' => 'pim_catalog_boolean',
            'group' => 'other',
        ]);

        $this->createAttribute([
            'code' => 'a_fifth_attribute',
            'type' => 'pim_catalog_boolean',
            'group' => 'other',
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => [
                'sku',
                'an_attribute',
                'a_second_attribute',
                'a_forth_attribute',
                'a_fifth_attribute',
            ],
        ]);

        $this->createFamily([
            'code' => 'a_second_family',
            'attributes' => [
                'sku',
                'a_second_attribute',
                'a_third_attribute',
            ],
        ]);

        $this->createFamilyVariant([
            'code' => 'a_family_variant',
            'variant_attribute_sets' => [
                [
                    'axes' => ['a_forth_attribute'],
                    'attributes' => [],
                    'level' => 1,
                ],
            ],
            'family' => 'a_family',
        ]);

        $this->createFamilyVariant([
            'code' => 'a_second_family_variant',
            'variant_attribute_sets' => [
                [
                    'axes' => ['a_forth_attribute'],
                    'attributes' => ['an_attribute'],
                    'level' => 1,
                ],
                [
                    'axes' => ['a_fifth_attribute'],
                    'attributes' => ['a_second_attribute'],
                    'level' => 2,
                ],
            ],
            'family' => 'a_family',
        ]);

        // Simple product
        $this->createProduct([
            'identifier' => 'product_1',
            'family' => 'a_family',
            'values' => [
                'an_attribute' => [
                    [
                        'data' => 'foo',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
                'a_second_attribute' => [
                    [
                        'data' => 'bar',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        // Simple product
        $this->createProduct([
            'identifier' => 'product_2',
            'family' => 'a_second_family',
            'values' => [
                'a_second_attribute' => [
                    [
                        'data' => 'foo',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
                'a_third_attribute' => [
                    [
                        'data' => 'foobar',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        // Product model with only one level of variations
        $this->createProductModel([
            'code' => 'a_product_model',
            'family_variant' => 'a_family_variant',
            'values' => [
                'an_attribute' => [
                    [
                        'data' => 'foo',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
                'a_second_attribute' => [
                    [
                        'data' => 'bar',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        // Product model with only two level of variations
        $this->createProductModel([
            'code' => 'a_second_product_model',
            'family_variant' => 'a_second_family_variant',
            'values' => [],
        ]);

        //Sub product model for the second level of variations
        $this->createProductModel([
            'code' => 'a_sub_product_model',
            'parent' => 'a_second_product_model',
            'values' => [
                'an_attribute' => [
                    [
                        'data' => 'foo',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
                'a_forth_attribute' => [
                    [
                        'data' => true,
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        // Variant product for the two level of variations
        $this->createProduct([
            'identifier' => 'product_3',
            'parent' => 'a_sub_product_model',
            'values' => [
                'a_second_attribute' => [
                    [
                        'data' => 'foo',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
                'a_fifth_attribute' => [
                    [
                        'data' => true,
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);
    }

    protected function createProduct(array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('new_product_' . rand());
        $this->get('pim_catalog.updater.product')->update($product, $data);

        /** @var ConstraintViolationList $constraintList */
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        foreach ($constraintList as $constraintViolation) {
            dump($constraintViolation->getMessage(), $constraintViolation->getPropertyPath(), $constraintViolation->getInvalidValue());
        }
        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a product');


        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    protected function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        /** @var ConstraintViolationList $constraintList */
        $constraintList = $this->get('pim_catalog.validator.product')->validate($productModel);
        foreach ($constraintList as $constraintViolation) {
            dump($constraintViolation->getMessage(), $constraintViolation->getPropertyPath());
        }

        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a product model');
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $productModel;
    }

    protected function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);

        /** @var ConstraintViolationList $constraintList */
        $constraintList = $this->get('validator')->validate($family);
        foreach ($constraintList as $constraintViolation) {
            dump($constraintViolation->getMessage(), $constraintViolation->getPropertyPath());
        }

        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a family variant');
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    protected function createFamily(array $data = []): FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);

        /** @var ConstraintViolationList $constraintList */
        $constraintList = $this->get('validator')->validate($family);
        foreach ($constraintList as $constraintViolation) {
            dump($constraintViolation->getMessage(), $constraintViolation->getPropertyPath(), $constraintViolation->getInvalidValue());
        }

        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a family');
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createAttribute(array $data = []): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        /** @var ConstraintViolationList $constraintList */
        $constraintList = $this->get('validator')->validate($attribute);
        foreach ($constraintList as $constraintViolation) {
            dump($constraintViolation->getMessage(), $constraintViolation->getPropertyPath(), $constraintViolation->getInvalidValue());
        }

        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create an attribute');
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function removeAttribute(string $attributeCode)
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneBy(['code' => $attributeCode]);

        if (null == $attribute) {
            throw new \LogicException(sprintf('Attribute %s not found', $attributeCode));
        }

        $this->get('pim_catalog.remover.attribute')->remove($attribute);
    }
}
