<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\ParentFieldSetter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParentFieldSetterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $productModelRepository)
    {
        $this->beConstructedWith($productModelRepository, ['parent']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParentFieldSetter::class);
    }

    function it_is_a_field_setter()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_only_work_with_parent_field()
    {
        $this->supportsField('parent')->shouldReturn(true);
        $this->supportsField('family')->shouldReturn(false);
    }

    function it_set_the_parent_to_a_variant_product(
        $productModelRepository,
        ProductInterface $variantProduct,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $productModelRepository->findOneByIdentifier('parent_code')->willReturn($productModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);

        $variantProduct->setParent($productModel)->shouldBeCalled();
        $variantProduct->setFamilyVariant($familyVariant)->shouldBeCalled();
        $variantProduct->getFamily()->willReturn($family);

        $this->setFieldData($variantProduct, 'parent', 'parent_code');
    }

    function it_sets_the_variant_product_s_parent_family_if_none(
        $productModelRepository,
        ProductInterface $variantProduct,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $productModelRepository->findOneByIdentifier('parent_code')->willReturn($productModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);

        $variantProduct->setParent($productModel)->shouldBeCalled();
        $variantProduct->setFamilyVariant($familyVariant)->shouldBeCalled();
        $variantProduct->getFamily()->willReturn(null);
        $variantProduct->setFamily($family)->shouldBeCalled();

        $this->setFieldData($variantProduct, 'parent', 'parent_code');
    }

    function it_throws_exception_if_the_provided_object_is_not_a_product(ProductModelInterface $productModel)
    {
        $this->shouldThrow(InvalidObjectException::class)->during(
            'setFieldData',
            [$productModel, 'parent', 'parent_code']
        );
    }

    function it_throws_exception_if_the_parent_code_does_not_match_an_existing_product_model_code(
        $productModelRepository,
        ProductInterface $variantProduct
    ) {
        $variantProduct->isVariant()->willReturn(true);
        $productModelRepository->findOneByIdentifier('parent_code')->willReturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during(
            'setFieldData',
            [$variantProduct, 'parent', 'parent_code']
        );
    }

    function it_throws_an_exception_if_the_family_variant_of_the_new_variant_product_parent_is_different(
        $productModelRepository,
        ProductInterface $variantProduct,
        ProductModelInterface $productModel,
        FamilyVariantInterface $previousFamilyVariant,
        FamilyVariantInterface $familyVariant
    ) {
        $productModelRepository->findOneByIdentifier('parent_code')->willReturn($productModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getFamilyVariant()->willReturn($previousFamilyVariant);

        $productModel->getCode()->willReturn('parent_code');
        $variantProduct->getIdentifier()->willReturn('variant_product');
        $previousFamilyVariant->getCode()->willReturn('previous_family_variant');

        $this->shouldThrow(InvalidPropertyException::class)->during(
            'setFieldData',
            [$variantProduct, 'parent', 'parent_code']
        );
    }

    function it_throw_an_exception_if_the_parent_is_empty_on_a_product_with_a_parent(
        ProductInterface $variantProduct
    ) {
        $variantProduct->isVariant()->willReturn(true);

        $this->shouldThrow(ImmutablePropertyException::class)->during(
            'setFieldData',
            [$variantProduct, 'parent', null]
        );
    }
}
