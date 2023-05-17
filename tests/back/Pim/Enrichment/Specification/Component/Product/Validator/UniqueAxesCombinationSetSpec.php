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

class UniqueAxesCombinationSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueAxesCombinationSet::class);
    }

    function it_adds_combinations_of_axis_values()
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

        $variantProductBBis = new Product(strtoupper($variantProductB->getUuid()->toString()));
        $variantProductBBis->addValue($identifierB);
        $variantProductBBis->setIdentifier('product_b');
        $variantProductBBis->setFamilyVariant($familyVariant);
        $variantProductBBis->setParent($productModel);

        $this->addCombination($productModel, '[a_color]');
        $this->addCombination($anotherProductModel, '[another_color]');
        $this->addCombination($variantProductA, '[a_size]');
        $this->addCombination($variantProductB, '[another_size]');
        $this->addCombination($variantProductBBis, '[another_size]');
    }

    function it_does_not_add_same_combination_of_axis_values_twice_for_product_models()
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

    function it_does_not_add_same_combination_of_axis_values_twice_for_variant_products()
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

        $invalidIdentifier = ScalarValue::value('sku', 'invalid_product');

        $invalidVariantProduct = new Product();
        $invalidVariantProduct->addValue($invalidIdentifier);
        $invalidVariantProduct->setIdentifier('invalid_product');
        $invalidVariantProduct->setFamilyVariant($familyVariant);
        $invalidVariantProduct->setParent($productModel);

        $this->addCombination($variantProduct, '[a_color]');

        $exception = new AlreadyExistingAxisValueCombinationException(
            'valid_variant_product',
            'Variant product "valid_variant_product" already have the "[a_color]" combination of axis values.'
        );
        $this
            ->shouldThrow($exception)
            ->during('addCombination', [$invalidVariantProduct, '[a_color]']);
    }

    function it_does_not_add_same_combination_of_axis_values_twice_for_variant_products_without_identifier()
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('family_variant');

        $productModel = new ProductModel();
        $productModel->setCode('root_product_model');
        $productModel->setFamilyVariant($familyVariant);

        $variantProduct = new Product('3d12c8f1-a048-4133-b22e-5ebbf0a45664');
        $variantProduct->setParent($productModel);
        $variantProduct->setFamilyVariant($familyVariant);

        $duplicateProduct = new Product();
        $duplicateProduct->setParent($productModel);
        $duplicateProduct->setFamilyVariant($familyVariant);

        $this->addCombination($variantProduct, '[optionA],1');

        $exception = new AlreadyExistingAxisValueCombinationException(
            '3d12c8f1-a048-4133-b22e-5ebbf0a45664',
            'Variant product "3d12c8f1-a048-4133-b22e-5ebbf0a45664" already have the "[optionA],1" combination of axis values.'
        );
        $this->shouldThrow($exception)
             ->during('addCombination', [$duplicateProduct, '[optionA],1']);
    }

    function it_does_not_add_same_combination_of_axis_values_twice_for_variant_products_with_identifier_like_a_uuid()
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('family_variant');

        $productModel = new ProductModel();
        $productModel->setCode('root_product_model');
        $productModel->setFamilyVariant($familyVariant);

        $variantProduct = new Product();
        $variantProduct->setIdentifier('4f8db754-4eff-4b67-bdba-6eac4d17f622');
        $variantProduct->setParent($productModel);
        $variantProduct->setFamilyVariant($familyVariant);

        $duplicateProduct = new Product('4f8db754-4eff-4b67-bdba-6eac4d17f622');
        $duplicateProduct->setParent($productModel);
        $duplicateProduct->setFamilyVariant($familyVariant);

        $this->addCombination($variantProduct, '[optionA],1');

        $exception = new AlreadyExistingAxisValueCombinationException(
            '4f8db754-4eff-4b67-bdba-6eac4d17f622',
            'Variant product "4f8db754-4eff-4b67-bdba-6eac4d17f622" already have the "[OptionA],1" combination of axis values.'
        );
        $this->shouldThrow($exception)
            ->during('addCombination', [$duplicateProduct, '[OptionA],1']);
    }
}
