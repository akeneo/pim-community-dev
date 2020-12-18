<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateEntityWithDefaultValuesIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_sets_the_default_boolean_values_of_a_new_simple_product()
    {
        $product = $this->createProduct('new_product', ['family' => 'familyA']);
        Assert::assertNull($product->getValue('a_yes_no_with_default_value'));

        $product = $this->saveProduct($product);

        $value = $product->getValue('a_yes_no_with_default_value');
        Assert::assertInstanceOf(ValueInterface::class, $value);
        Assert::assertSame(true, $value->getData());
    }

    /**
     * @test
     */
    public function it_sets_the_default_values_on_a_new_root_product_model()
    {
        $productModel = $this->createProductModel([
            'code' => 'root',
            'parent' => null,
            'family_variant' => 'familyVariantA1',
        ]);
        Assert::assertNull($productModel->getValue('a_yes_no_with_default_value'));

        $productModel = $this->saveProductModel($productModel);
        $value = $productModel->getValue('a_yes_no_with_default_value');
        Assert::assertInstanceOf(ValueInterface::class, $value);
        Assert::assertSame(true, $value->getData());
    }

    /**
     * @test
     */
    public function it_sets_the_default_values_on_a_new_sub_product_model()
    {
        // move the 'a_yes_no_with_default_value' attribute to level 1 (= sub product model level)
        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('familyVariantA1');
        $attributeSet = $familyVariant->getVariantAttributeSet(1);
        $attributeSet->addAttribute(
            $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_yes_no_with_default_value')
        );
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $root = $this->createProductModel(
            [
                'code' => 'root',
                'parent' => null,
                'family_variant' => 'familyVariantA1',
            ]
        );
        Assert::assertNull($root->getValue('a_yes_no_with_default_value'));
        $root = $this->saveProductModel($root);
        Assert::assertNull($root->getValue('a_yes_no_with_default_value'));

        $sub = $this->createProductModel([
            'code' => 'sub',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root',
            'values' => [
                'a_simple_select' => [['data' => 'optionA', 'scope' => null, 'locale' => null]],
            ],
        ]);
        Assert::assertNull($sub->getValue('a_yes_no_with_default_value'));
        $sub = $this->saveProductModel($sub);

        $value = $sub->getValue('a_yes_no_with_default_value');
        Assert::assertInstanceOf(ValueInterface::class, $value);
        Assert::assertSame(true, $value->getData());
    }

    /**
     * @test
     */
    public function it_sets_the_default_values_on_a_new_variant_product()
    {
        // move the 'a_yes_no_with_default_value' attribute to level 1 (= variant product level)
        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier('familyVariantA2');
        $attributeSet = $familyVariant->getVariantAttributeSet(1);
        $attributeSet->addAttribute(
            $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_yes_no_with_default_value')
        );
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        $productModel = $this->createProductModel(
            [
                'code' => 'root',
                'parent' => null,
                'family_variant' => 'familyVariantA2',
            ]
        );
        Assert::assertNull($productModel->getValue('a_yes_no_with_default_value'));
        $productModel = $this->saveProductModel($productModel);
        Assert::assertNull($productModel->getValue('a_yes_no_with_default_value'));

        $variantProduct = $this->createProduct('variant', [
            'family' => 'familyA',
            'parent' => 'root',
            'values' => [
                'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]],
            ]
        ]);
        Assert::assertNull($variantProduct->getValue('a_yes_no_with_default_value'));
        $variantProduct = $this->saveProduct($variantProduct);

        $value = $variantProduct->getValue('a_yes_no_with_default_value');
        Assert::assertInstanceOf(ValueInterface::class, $value);
        Assert::assertSame(true, $value->getData());
    }

    /**
     * @test
     */
    public function it_does_not_override_already_defined_values()
    {
        $product = $this->createProduct('new_product', [
            'family' => 'familyA',
            'values' => [
                'a_yes_no_with_default_value' => [['data' => false, 'locale' => null, 'scope' => null]],
            ]
        ]);
        $value = $product->getValue('a_yes_no_with_default_value');
        Assert::assertInstanceOf(ValueInterface::class, $value);
        Assert::assertSame(false, $value->getData());

        $product = $this->saveProduct($product);
        $value = $product->getValue('a_yes_no_with_default_value');
        Assert::assertInstanceOf(ValueInterface::class, $value);
        Assert::assertSame(false, $value->getData());
    }

    /**
     * @test
     */
    public function it_does_not_set_default_values_on_update()
    {
        $product = $this->createProduct('updated', []);
        $product = $this->saveProduct($product);
        // no family, so the default value is not set
        Assert::assertNull($product->getValue('a_yes_no_with_default_value'));

        $this->get('pim_catalog.updater.product')->update($product, ['family' => 'familyA']);
        $product = $this->saveProduct($product);
        // this is an update, so the default values are not set
        Assert::assertNull($product->getValue('a_yes_no_with_default_value'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $attribute = $this->createAttribute([
            'code' => 'a_yes_no_with_default_value',
            'type' => 'pim_catalog_boolean',
            'group' => 'attributeGroupA',
            'default_value' => true,
        ]);
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $family->addAttribute($attribute);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createAttribute(array $data): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $violations = $this->get('validator')->validate($attribute);
        Assert::assertEmpty($violations);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        return $product;
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        return $productModel;
    }

    private function saveProduct(ProductInterface $product): ProductInterface
    {
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product has validation errors: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($product->getIdentifier());
    }

    private function saveProductModel(ProductModelInterface $productModel): ProductModelInterface
    {
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model has validation errors: %s', $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        return $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($productModel->getIdentifier());
    }
}
