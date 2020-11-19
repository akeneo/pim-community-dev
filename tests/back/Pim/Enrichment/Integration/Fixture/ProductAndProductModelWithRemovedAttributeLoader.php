<?php


namespace AkeneoTest\Pim\Enrichment\Integration\Fixture;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductAndProductModelWithRemovedAttributeLoader
{
    private $productFactory;
    private $productUpdater;
    private $productValidator;
    private $productSaver;
    private $productModelFactory;
    private $productModelUpdater;
    private $productModelValidator;
    private $productModelSaver;
    private $familyVariantFactory;
    private $familyVariantUpdater;
    private $familyVariantSaver;
    private $familyFactory;
    private $familyUpdater;
    private $familySaver;
    private $attributeFactory;
    private $attributeUpdater;
    private $attributeSaver;
    private $attributeRepository;
    private $attributeRemover;
    private $entityValidator;
    private $productAndProductModelEsClient;

    public function __construct(
        ProductBuilder $productFactory,
        ProductUpdater $productUpdater,
        ValidatorInterface $productValidator,
        ProductSaver $productSaver,
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        ValidatorInterface $productModelValidator,
        BaseSaver $productModelSaver,
        SimpleFactoryInterface $familyVariantFactory,
        ObjectUpdaterInterface $familyVariantUpdater,
        BaseSaver $familyVariantSaver,
        SimpleFactoryInterface $familyFactory,
        ObjectUpdaterInterface $familyUpdater,
        FamilySaver $familySaver,
        SimpleFactoryInterface $attributeFactory,
        ObjectUpdaterInterface $attributeUpdater,
        AttributeSaver $attributeSaver,
        AttributeRepository $attributeRepository,
        BaseRemover $attributeRemover,
        ValidatorInterface $entityValidator,
        Client $productAndProductModelEsClient
    )
    {

        $this->productFactory = $productFactory;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->productSaver = $productSaver;
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelValidator = $productModelValidator;
        $this->productModelSaver = $productModelSaver;
        $this->familyVariantFactory = $familyVariantFactory;
        $this->familyVariantUpdater = $familyVariantUpdater;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->familyFactory = $familyFactory;
        $this->familyUpdater = $familyUpdater;
        $this->familySaver = $familySaver;
        $this->attributeFactory = $attributeFactory;
        $this->attributeUpdater = $attributeUpdater;
        $this->attributeSaver = $attributeSaver;
        $this->attributeRepository = $attributeRepository;
        $this->attributeRemover = $attributeRemover;
        $this->entityValidator = $entityValidator;
        $this->productAndProductModelEsClient = $productAndProductModelEsClient;
    }

    public function load(): void
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

        // Simple product
        $this->createProduct([
            'identifier' => 'product_4',
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
                        'data' => 'barfoo',
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

        $this->removeAttribute('an_attribute');
        $this->removeAttribute('a_third_attribute');
    }

    protected function createProduct(array $data = []): ProductInterface
    {
        $identifier = $data['identifier'] ?? 'new_product_' . rand();

        $product = $this->productFactory->createProduct($identifier);
        $this->productUpdater->update($product, $data);
        $constraintList = $this->productValidator->validate($product);
        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a product');
        $this->productSaver->save($product);
        $this->productAndProductModelEsClient->refreshIndex();

        return $product;
    }

    protected function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($productModel, $data);
        $constraintList = $this->productModelValidator->validate($productModel);
        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a product model');
        $this->productModelSaver->save($productModel);
        $this->productAndProductModelEsClient->refreshIndex();

        return $productModel;
    }

    protected function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update($family, $data);
        $constraintList = $this->entityValidator->validate($family);
        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a family variant');
        $this->familyVariantSaver->save($family);

        return $family;
    }

    protected function createFamily(array $data = []): FamilyInterface
    {
        $family = $this->familyFactory->create();
        $this->familyUpdater->update($family, $data);
        $constraintList = $this->entityValidator->validate($family);
        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create a family');
        $this->familySaver->save($family);

        return $family;
    }

    protected function createAttribute(array $data = []): AttributeInterface
    {
        $attribute = $this->attributeFactory->create();
        $this->attributeUpdater->update($attribute, $data);
        $constraintList = $this->entityValidator->validate($attribute);
        Assert::assertEquals(0, $constraintList->count(), 'Impossible to create an attribute');
        $this->attributeSaver->save($attribute);

        return $attribute;
    }

    public function removeAttribute(string $attributeCode)
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => $attributeCode]);

        if (null == $attribute) {
            throw new \LogicException(sprintf('Attribute %s not found', $attributeCode));
        }

        $this->attributeRemover->remove($attribute);
    }
}
