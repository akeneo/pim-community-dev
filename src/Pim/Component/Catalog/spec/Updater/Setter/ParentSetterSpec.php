<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use Pim\Component\Catalog\Updater\Setter\ParentSetter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParentSetterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $productModelRepository)
    {
        $this->beConstructedWith($productModelRepository, ['parent']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParentSetter::class);
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
        VariantProductInterface $product,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $productModelRepository->findOneByIdentifier('parent_code')->willReturn($productModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);

        $product->setParent($productModel)->shouldBeCalled();
        $product->setFamilyVariant($familyVariant)->shouldBeCalled();
        $product->getFamily()->willReturn($family);

        $this->setFieldData($product, 'parent', 'parent_code')->shouldReturn(null);
    }

    function it_sets_the_variant_product_s_parent_family_if_none(
        $productModelRepository,
        VariantProductInterface $product,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $productModelRepository->findOneByIdentifier('parent_code')->willReturn($productModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);

        $product->setParent($productModel)->shouldBeCalled();
        $product->setFamilyVariant($familyVariant)->shouldBeCalled();
        $product->getFamily()->willReturn(null);
        $product->setFamily($family)->shouldBeCalled();

        $this->setFieldData($product, 'parent', 'parent_code')->shouldReturn(null);
    }

    function it_throws_exception_if_the_provided_object_is_not_a_product(ProductModelInterface $productModel)
    {
        $this->shouldThrow(InvalidObjectException::class)->during(
            'setFieldData',
            [$productModel, 'parent', 'parent_code']
        );
    }

    function it_throws_exception_if_the_parent_code_does_not_an_existing_product_model_code(
        $productModelRepository,
        VariantProductInterface $variantProduct
    ) {
        $productModelRepository->findOneByIdentifier('parent_code')->willReturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during(
            'setFieldData',
            [$variantProduct, 'parent', 'parent_code']
        );
    }
}
