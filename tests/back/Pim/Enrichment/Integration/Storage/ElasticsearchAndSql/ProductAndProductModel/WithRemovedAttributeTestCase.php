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

abstract class WithRemovedAttributeTestCase extends TestCase
{
    const ATTRIBUTE_ONLY_ON_ONE_PRODUCT = 'attribute_only_on_one_product';
    const ATTRIBUTE_ONLY_ON_ONE_PRODUCT_MODEL = 'attribute_only_on_one_product_model';
    const ATTRIBUTE_ONLY_ON_ONE_SUB_PRODUCT_MODEL = 'attribute_only_on_one_sub_product_model';
    const ATTRIBUTE_ONLY_ON_ONE_PRODUCT_VARIANT = 'attribute_only_on_one_product_variant';

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function loadFixtures(): void
    {
        $family = $this->createFamily([
            'code' => 'family_'.uniqid(),
        ]);

        $this->createAttribute([
            'code' => self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT,
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->createAttribute([
            'code' => self::ATTRIBUTE_ONLY_ON_ONE_SUB_PRODUCT_MODEL,
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->createAttribute([
            'code' => self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT_VARIANT,
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $this->createProduct([
            'identifier' => 'product_'.uniqid(),
            'family' => $family->getCode(),
            'values' => [
                self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT => [
                    [
                        'data' => 'foobar',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        $attributeFirstAxis = $this->createAttribute([
            'code' => 'attribute_axis_'.uniqid(),
            'type' => 'pim_catalog_boolean',
            'group' => 'other',
        ]);
        $attributeSecondAxis = $this->createAttribute([
            'code' => 'attribute_axis_'.uniqid(),
            'type' => 'pim_catalog_boolean',
            'group' => 'other',
        ]);
        $attributeOnlyOnProductModels = $this->createAttribute([
            'code' => self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT_MODEL,
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);

        $family->addAttribute($attributeFirstAxis);
        $family->addAttribute($attributeSecondAxis);
        $family->addAttribute($attributeOnlyOnProductModels);

        $this->get('pim_catalog.saver.family')->save($family);

        $familyVariantWithOneLevel = $this->createFamilyVariant([
            'code' => 'family_variant_'.uniqid(),
            'variant_attribute_sets' => [
                [
                    'axes' => [$attributeFirstAxis->getCode()],
                    'attributes' => [self::ATTRIBUTE_ONLY_ON_ONE_SUB_PRODUCT_MODEL],
                    'level' => 1,
                ],
            ],
            'family' => $family->getCode(),
        ]);

        $familyVariantWithTwoLevels = $this->createFamilyVariant([
            'code' => 'family_variant_'.uniqid(),
            'variant_attribute_sets' => [
                [
                    'axes' => [$attributeFirstAxis->getCode()],
                    'attributes' => [self::ATTRIBUTE_ONLY_ON_ONE_SUB_PRODUCT_MODEL],
                    'level' => 1,
                ],
                [
                    'axes' => [$attributeSecondAxis->getCode()],
                    'attributes' => [self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT_VARIANT],
                    'level' => 2,
                ],
            ],
            'family' => $family->getCode(),
        ]);

        $productModelWithoutVariants = $this->createProductModel([
            'code' => 'product_model_'.rand(),
            'family_variant' => $familyVariantWithOneLevel->getCode(),
            'values' => [
                self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT_MODEL => [
                    [
                        'data' => 'foobar',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        $productModelWithOneVariant = $this->createProductModel([
            'code' => 'product_model_'.rand(),
            'family_variant' => $familyVariantWithOneLevel->getCode(),
            'values' => [],
        ]);

        $this->createVariantProduct('new_variant_product_'.rand(), [
            'categories' => ['master'],
            'parent' => $productModelWithOneVariant->getCode(),
            'values' => [
                $attributeFirstAxis->getCode() => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'true',
                    ],
                ],
            ],
        ]);

        $productModelWithOneSubModel = $this->createProductModel([
            'code' => 'product_model_'.rand(),
            'family_variant' => $familyVariantWithOneLevel->getCode(),
            'values' => [],
        ]);

        $subProductModel = $this->createProductModel([
            'code' => 'product_model_'.rand(),
            'family_variant' => $familyVariantWithTwoLevels->getCode(),
            'parent' => $productModelWithOneSubModel->getCode(),
            'values' => [
                $attributeFirstAxis->getCode() => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'true',
                    ],
                ],
                self::ATTRIBUTE_ONLY_ON_ONE_SUB_PRODUCT_MODEL => [
                    [
                        'data' => 'foobar',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
        ]);

        $this->createVariantProduct('new_variant_product_'.rand(), [
            'parent' => $subProductModel->getCode(),
            'values' => [
                $attributeSecondAxis->getCode() => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'true',
                    ],
                ],
            ],
        ]);
    }

    private function createProduct(array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('new_product_'.rand());
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertEquals(0, $constraintList->count());

        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    private function createVariantProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    protected function createRandomProductWithAttributes(array $attributesCodes): ProductInterface
    {
        $family = $this->createFamily([
            'code' => 'family_'.uniqid(),
        ]);

        $values = [];
        foreach ($attributesCodes as $attributeCode) {
            $values[$attributeCode] = [
                [
                    'data' => 'some text',
                    'locale' => null,
                    'scope' => null,
                ],
            ];
        }

        return $this->createProduct([
            'identifier' => 'new_product_'.uniqid(),
            'family' => $family->getCode(),
            'values' => $values,
        ]);
    }

    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($productModel);
        Assert::assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $productModel;
    }

    protected function createRandomProductModelWithAttributes(array $attributesCodes): ProductModelInterface
    {
        $axisAttribute = $this->createAttribute([
            'code' => 'new_attribute_axis_'.uniqid(),
            'type' => 'pim_catalog_boolean',
            'group' => 'other',
        ]);

        $family = $this->createFamily([
            'code' => 'new_family_'.uniqid(),
        ]);
        $family->addAttribute($axisAttribute);

        $values = [];
        foreach ($attributesCodes as $attributeCode) {
            $values[$attributeCode] = [
                [
                    'data' => 'some text',
                    'locale' => null,
                    'scope' => null,
                ],
            ];
            $attribute = $this->get('pim_catalog.repository.attribute')->findOneBy(['code' => $attributeCode]);
            $family->addAttribute($attribute);
        }

        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);

        $familyVariant = $this->createFamilyVariant([
            'code' => 'new_family_variant_'.uniqid(),
            'variant_attribute_sets' => [
                [
                    'axes' => [$axisAttribute->getCode()],
                    'attributes' => [],
                    'level' => 1,
                ],
            ],
            'family' => $family->getCode(),
        ]);

        return $this->createProductModel([
            'code' => 'new_product_model_'.rand(),
            'family_variant' => $familyVariant->getCode(),
            'values' => $values,
        ]);
    }

    protected function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        Assert::assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

//    protected function createProductModelsWithAttributes(int $count, array $attributesCodes)
//    {
//        $axisAttribute = $this->createAttribute([
//            'code' => 'new_attribute_'.uniqid(),
//            'type' => 'pim_catalog_boolean',
//            'group' => 'other',
//        ]);
//
//        $family = $this->createFamily(['code' => 'new_family_'.uniqid()]);
//        $family->addAttribute($axisAttribute);
//
//        $productModelValues = [];
//        $i = 0;
//        while ($i < $numberOfProductValues) {
//            $attribute = $this->createAttribute([
//                'code' => 'new_attribute_'.rand(),
//                'type' => 'pim_catalog_text',
//                'group' => 'other',
//            ]);
//
//            $family->addAttribute($attribute);
//            $productModelValues[$attribute->getCode()] = [
//                ['data' => rand().' some text random', 'locale' => null, 'scope' => null],
//            ];
//            $i++;
//        }
//
//        $errors = $this->get('validator')->validate($family);
//        Assert::assertCount(0, $errors);
//
//        $this->get('pim_catalog.saver.family')->save($family);
//
//        $familyVariant = $this->createFamilyVariant([
//            'code' => 'new_family_variant_'.rand(),
//            'variant_attribute_sets' => [
//                [
//                    'axes' => [$axisAttribute->getCode()],
//                    'attributes' => [],
//                    'level' => 1,
//                ],
//            ],
//            'family' => $family->getCode(),
//        ]);
//
//        $this->createProductModel([
//            'code' => 'new_product_model_'.rand(),
//            'family_variant' => $familyVariant->getCode(),
//            'values' => $productModelValues,
//        ]);
//    }

    private function createFamily(array $data = []): FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        Assert::assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createTextAttribute(string $code): AttributeInterface
    {
        return $this->createAttribute([
            'code' => $code,
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);
    }

    protected function createAttribute(array $data = []): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraintList = $this->get('validator')->validate($attribute);
        Assert::assertEquals(0, $constraintList->count());
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
