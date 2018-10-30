<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FamilyFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class FamilyFieldSetterSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->beConstructedWith(
            $familyRepository,
            ['family']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement(SetterInterface::class);
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_supports_family_field()
    {
        $this->supportsField('family')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'family',
                FamilyFieldSetter::class,
                ['not a string']
            )
        )->during('setFieldData', [$product, 'family', ['not a string']]);
    }

    function it_sets_family_field(
        $familyRepository,
        ProductInterface $product,
        FamilyInterface $shirt
    ) {
        $familyRepository->findOneByIdentifier('shirt')->willReturn($shirt);
        $product->setFamily($shirt)->shouldBeCalled();

        $this->setFieldData($product, 'family', 'shirt');
    }

    function it_empty_family_field(ProductInterface $product) {
        $product->setFamily(null)->shouldBeCalled();
        $this->setFieldData($product, 'family', null);
    }

    function it_fails_if_the_family_code_is_not_a_valid_family_code(
        $familyRepository,
        ProductInterface $product
    ) {
        $familyRepository->findOneByIdentifier('shirt')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'family',
                'family code',
                'The family does not exist',
                FamilyFieldSetter::class,
                'shirt'
            )
        )->during('setFieldData', [$product, 'family', 'shirt']);
    }
}
