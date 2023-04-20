<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueAxesCombinationSet;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Ramsey\Uuid\Uuid;

class UniqueAxesCombinationSetSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UniqueAxesCombinationSet::class);
    }

    public function it_adds_combinations_of_axis_values(): void
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('family_variant');

        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root_product_model');
        $rootProductModel->setFamilyVariant($familyVariant);

        $productModel = new ProductModel();
        $productModel->setCode('product_model');
        $productModel->setFamilyVariant($familyVariant);
        $productModel->setParent($rootProductModel);

        $anotherProductModel = new ProductModel();
        $anotherProductModel->setCode('another_product_model');
        $anotherProductModel->setFamilyVariant($familyVariant);
        $anotherProductModel->setParent($rootProductModel);

        $identifierAttribute = new Attribute();
        $identifierAttribute->setCode('sku');
        $identifierA = ScalarValue::value('sku', 'product_a');

        $variantProductA = new Product();
        $variantProductA->addValue($identifierA);
        $variantProductA->setIdentifier('product_a');
        $variantProductA->setFamilyVariant($familyVariant);
        $variantProductA->setParent($productModel);

        $identifierB = ScalarValue::value('sku', 'product_b');

        $variantProductB = new Product();
        $variantProductB->addValue($identifierB);
        $variantProductB->setIdentifier('product_b');
        $variantProductB->setFamilyVariant($familyVariant);
        $variantProductB->setParent($productModel);

        $this->addCombination($productModel, '[a_color]');
        $this->addCombination($anotherProductModel, '[another_color]');
        $this->addCombination($variantProductA, '[a_size]');
        $this->addCombination($variantProductB, '[another_size]');
    }

    public function it_does_not_add_same_combination_of_axis_values_twice_for_product_models(): void
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('family_variant');

        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root_product_model');
        $rootProductModel->setFamilyVariant($familyVariant);

        $productModel = new ProductModel();
        $productModel->setCode('valid_product_model');
        $productModel->setFamilyVariant($familyVariant);
        $productModel->setParent($rootProductModel);

        $invalidProductModel = new ProductModel();
        $invalidProductModel->setCode('invalid_product_model');
        $invalidProductModel->setFamilyVariant($familyVariant);
        $invalidProductModel->setParent($rootProductModel);

        $this->addCombination($productModel, '[a_color]');

        $exception = new AlreadyExistingAxisValueCombinationException(
            'valid_product_model',
            'Product model "valid_product_model" already have the "[a_color]" combination of axis values.'
        );
        $this
            ->shouldThrow($exception)
            ->during('addCombination', [$invalidProductModel, '[a_color]']);
    }

    public function it_does_not_add_same_combination_of_axis_values_twice_for_variant_products(): void
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('family_variant');

        $productModel = new ProductModel();
        $productModel->setCode('root_product_model');
        $productModel->setFamilyVariant($familyVariant);

        $identifierAttribute = new Attribute();
        $identifierAttribute->setCode('sku');
        $identifier = ScalarValue::value('sku', 'valid_variant_product');

        $variantProduct = new Product();
        $variantProduct->addValue($identifier);
        $variantProduct->setIdentifier('valid_variant_product');
        $variantProduct->setFamilyVariant($familyVariant);
        $variantProduct->setParent($productModel);

        $this->addCombination($variantProduct, '[a_color]');

        $invalidVariantProductWithIdentifier = new Product();
        $invalidVariantProductWithIdentifier->addValue(ScalarValue::value('sku', 'invalid_product'));
        $invalidVariantProductWithIdentifier->setIdentifier('invalid_product');
        $invalidVariantProductWithIdentifier->setFamilyVariant($familyVariant);
        $invalidVariantProductWithIdentifier->setParent($productModel);

        $exception = new AlreadyExistingAxisValueCombinationException(
            'valid_variant_product',
            sprintf('Variant product "%s" already have the "[a_color]" combination of axis values.', 'valid_variant_product')
        );
        $this
            ->shouldThrow($exception)
            ->during('addCombination', [$invalidVariantProductWithIdentifier, '[a_color]']);

        $invalidProductUuid = Uuid::uuid4();
        $invalidVariantProductWithoutIdentifier = new Product($invalidProductUuid);
        $invalidVariantProductWithoutIdentifier->setFamilyVariant($familyVariant);
        $invalidVariantProductWithoutIdentifier->setParent($productModel);

        $exception2 = new AlreadyExistingAxisValueCombinationException(
            'valid_variant_product',
            sprintf('Variant product "%s" already have the "[a_color]" combination of axis values.', 'valid_variant_product')
        );
        $this
            ->shouldThrow($exception2)
            ->during('addCombination', [$invalidVariantProductWithoutIdentifier, '[a_color]']);
    }

    public function it_does_not_add_same_combination_of_axis_values_twice_for_variant_products_without_identifier(): void
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('family_variant');

        $productModel = new ProductModel();
        $productModel->setCode('root_product_model');
        $productModel->setFamilyVariant($familyVariant);

        $productVariantUuid = Uuid::uuid4();
        $variantProduct = new Product($productVariantUuid);
        $variantProduct->setFamilyVariant($familyVariant);
        $variantProduct->setParent($productModel);

        $this->addCombination($variantProduct, '[a_color]');

        $invalidVariantProductWithIdentifier = new Product();
        $invalidVariantProductWithIdentifier->addValue(ScalarValue::value('sku', 'invalid_product'));
        $invalidVariantProductWithIdentifier->setIdentifier('invalid_product');
        $invalidVariantProductWithIdentifier->setFamilyVariant($familyVariant);
        $invalidVariantProductWithIdentifier->setParent($productModel);

        $invalidVariantProductWithoutIdentifier = new Product(Uuid::uuid4());
        $invalidVariantProductWithoutIdentifier->setFamilyVariant($familyVariant);
        $invalidVariantProductWithoutIdentifier->setParent($productModel);

        $exception = new AlreadyExistingAxisValueCombinationException(
            $productVariantUuid->toString(),
            sprintf('Variant product "%s" already have the "[a_color]" combination of axis values.', $productVariantUuid->toString())
        );
        $this
            ->shouldThrow($exception)
            ->during('addCombination', [$invalidVariantProductWithIdentifier, '[a_color]']);
        $this
            ->shouldThrow($exception)
            ->during('addCombination', [$invalidVariantProductWithoutIdentifier, '[a_color]']);
    }
}
