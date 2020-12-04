<?php


namespace Specification\Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CleanValuesOfRemovedAttributesSpec extends ObjectBehavior
{
    public function let(
        CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute,
        CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute,
        CountProductsAndProductModelsWithInheritedRemovedAttributeInterface $countProductsAndProductModelsWithInheritedRemovedAttribute,
        GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute,
        GetProductModelIdentifiersWithRemovedAttributeInterface $getProductModelIdentifiersWithRemovedAttribute,
        ProductRepositoryInterface $productRepository,
        BulkSaverInterface $productSaver,
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productModelSaver,
        ValidatorInterface $validator,
        GetAttributes $getAttributes,
        UnitOfWorkAndRepositoriesClearer $clearer
    )
    {
        $this->beConstructedWith(
            $countProductsWithRemovedAttribute,
            $countProductModelsWithRemovedAttribute,
            $countProductsAndProductModelsWithInheritedRemovedAttribute,
            $getProductIdentifiersWithRemovedAttribute,
            $getProductModelIdentifiersWithRemovedAttribute,
            $productRepository,
            $productSaver,
            $productModelRepository,
            $productModelSaver,
            $validator,
            $getAttributes,
            $clearer
        );
    }


    public function it_counts_products_with_removed_attribute(
        $countProductsWithRemovedAttribute
    ): void
    {
        $removedAttributes = ['an_attribute', 'a_second_attribute', 'a_third_attribute'];

        $countProductsWithRemovedAttribute->count($removedAttributes)->willReturn(3);
        $this->countProductsWithRemovedAttribute($removedAttributes)->shouldBe(3);
    }

    public function it_counts_product_models_with_removed_attribute(
        $countProductModelsWithRemovedAttribute
    ): void
    {
        $removedAttributes = ['an_attribute', 'a_second_attribute', 'a_third_attribute'];

        $countProductModelsWithRemovedAttribute->count($removedAttributes)->willReturn(2);
        $this->countProductModelsWithRemovedAttribute($removedAttributes)->shouldBe(2);
    }

    public function it_counts_products_and_product_models_with_inherited_removed_attribute(
        $countProductsAndProductModelsWithInheritedRemovedAttribute
    ): void
    {
        $removedAttributes = ['an_attribute', 'a_second_attribute', 'a_third_attribute'];

        $countProductsAndProductModelsWithInheritedRemovedAttribute->count($removedAttributes)->willreturn(1);
        $this->countProductsAndProductModelsWithInheritedRemovedAttribute($removedAttributes)->shouldBe(1);
    }

    public function it_cleans_product_models_with_removed_attribute(
        $getProductModelIdentifiersWithRemovedAttribute,
        $productModelRepository,
        $productModelSaver,
        $clearer,
        ProductModelInterface $aProductModel,
        ProductModelInterface $aSecondProductModel,
        ProductModelInterface $aThirdProductModel
    ): void
    {
        $removedAttributes = ['an_attribute', 'a_second_attribute'];
        $codes = [
            ['a_product_model', 'a_second_product_model'],
            ['a_third_product_model'],
        ];
        $productModelsBatch1 = [$aProductModel, $aSecondProductModel];
        $productModelsBatch2 = [$aThirdProductModel];

        $getProductModelIdentifiersWithRemovedAttribute->nextBatch($removedAttributes, Argument::type('integer'))->willReturn($codes);

        $productModelRepository->findBy(['code' => $codes[0]])->willReturn($productModelsBatch1);
        $productModelSaver->saveAll($productModelsBatch1, ['force_save' => true])->shouldBeCalled();
        $clearer->clear()->shouldBeCalled();

        $productModelRepository->findBy(['code' => $codes[1]])->willReturn($productModelsBatch2);
        $productModelSaver->saveAll($productModelsBatch2, ['force_save' => true])->shouldBeCalled();
        $clearer->clear()->shouldBeCalled();

        $this->cleanProductModelsWithRemovedAttribute($removedAttributes, null);
    }

    public function it_cleans_products_with_removed_attribute(
        $getProductIdentifiersWithRemovedAttribute,
        $productRepository,
        $productSaver,
        $clearer,
        ProductInterface $aProduct,
        ProductInterface $aSecondProduct
    ): void
    {
        $removedAttributes = ['an_attribute', 'a_second_attribute', 'a_third_attribute'];
        $batchOfIdentifiers = [
            ['a_product', 'a_second_product'],
        ];
        $products = [$aProduct, $aSecondProduct];

        $getProductIdentifiersWithRemovedAttribute->nextBatch($removedAttributes, Argument::type('integer'))->willReturn($batchOfIdentifiers);
        $productRepository->findBy(['identifier' => ['a_product', 'a_second_product']])->willReturn($products);
        $productSaver->saveAll($products, ['force_save' => true])->shouldBeCalled();

        $clearer->clear()->shouldBeCalled();

        $this->cleanProductsWithRemovedAttribute($removedAttributes, null);
    }

    public function it_validates_removed_attributes_codes(
        $validator,
        $getAttributes
    ): void
    {
        $removedAttributes = ['an_attribute', 'a_second_attribute'];

        $validator->validate('an_attribute', Argument::cetera())->willReturn([]);
        $validator->validate('a_second_attribute', Argument::cetera())->willReturn([]);

        $this->validateRemovedAttributesCodes($removedAttributes);

        $getAttributes->forCode('an_attribute')->shouldHaveBeenCalled();
        $getAttributes->forCode('a_second_attribute')->shouldHaveBeenCalled();
    }

    public function it_does_not_validate_an_empty_list_of_removed_attributes_codes(): void
    {
        $removedAttributes = [];
        $this->shouldThrow(\LogicException::class)->during('validateRemovedAttributesCodes', [$removedAttributes]);
    }

    public function it_does_not_validate_when_list_containing_invalid_removed_attributes_code(
        $validator,
        $getAttributes,
        ConstraintViolation $anAttributeConstraintViolation
    ): void
    {
        $removedAttributes = ['an_attribute', 'an_invalid_attribute'];

        $validator->validate('an_attribute', Argument::cetera())->willReturn([]);
        $validator->validate('an_invalid_attribute', Argument::cetera())->willReturn([
            $anAttributeConstraintViolation
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('validateRemovedAttributesCodes', [$removedAttributes]);

        $getAttributes->forCode('an_attribute')->shouldHaveBeenCalled();
        $getAttributes->forCode('an_invalid_attribute')->shouldNotHaveBeenCalled();
    }

    public function it_does_not_validate_when_list_containing_existing_attributes_code(
        $validator,
        $getAttributes
    ): void
    {
        $anExistingAttribute = new Attribute(
            'an_existing_attribute',
            'an_attribute_type',
            [],
            false,
            false,
            null,
            null,
            false,
            'a_backend_type',
            []
        );
        $removedAttributes = ['an_attribute', 'an_existing_attribute'];

        $validator->validate('an_attribute', Argument::cetera())->willReturn([]);
        $validator->validate('an_existing_attribute', Argument::cetera())->willReturn([]);

        $getAttributes->forCode('an_attribute')->willReturn(null);
        $getAttributes->forCode('an_existing_attribute')->willReturn($anExistingAttribute);

        $this->shouldThrow(\InvalidArgumentException::class)->during('validateRemovedAttributesCodes', [$removedAttributes]);
    }
}
