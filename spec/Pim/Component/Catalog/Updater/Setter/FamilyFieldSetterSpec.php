<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;

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
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\FieldSetterInterface');
    }

    function it_supports_family_field()
    {
        $this->supportsField('family')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidArgumentException::stringExpected(
                'family',
                'setter',
                'family',
                'array'
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

    function it_empty_family_field(
        $familyRepository,
        ProductInterface $product,
        FamilyInterface $shirt
    ) {
        $product->setFamily(null)->shouldBeCalled();
        $this->setFieldData($product, 'family', null);
    }

    function it_fails_if_the_family_code_is_not_a_valid_family_code(
        $familyRepository,
        ProductInterface $product
    ) {
        $familyRepository->findOneByIdentifier('shirt')->willReturn(null);

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'family',
                'existing family code',
                'setter',
                'family',
                'shirt'
            )
        )->during('setFieldData', [$product, 'family', 'shirt']);
    }
}
