<?php

namespace spec\Akeneo\Test\Acceptance\FamilyVariant;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\Acceptance\FamilyVariant\InMemoryFamilyVariantRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Prophecy\Argument;

class InMemoryFamilyVariantRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryFamilyVariantRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_family_variant_repository()
    {
        $this->shouldBeAnInstanceOf(FamilyVariantRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_asserts_the_identifier_property_is_the_code()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_family_variant_by_identifier()
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('a-family-variant');
        $this->beConstructedWith([$familyVariant->getCode() => $familyVariant]);

        $this->findOneByIdentifier('a-family-variant')->shouldReturn($familyVariant);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-family-variant')->shouldReturn(null);
    }

    function it_saves_a_family_variant()
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('a-family-variant');

        $this->save($familyVariant)->shouldReturn(null);

        $this->findOneByIdentifier($familyVariant->getCode())->shouldReturn($familyVariant);
    }

    function it_saves_only_family_variants()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }

    function it_asserts_that_the_other_methods_are_not_implemented_yet()
    {
        $this->shouldThrow(NotImplementedException::class)->during('find', ['a-family']);
        $this->shouldThrow(NotImplementedException::class)->during('findAll', []);
        $this->shouldThrow(NotImplementedException::class)->during('findBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('findOneBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('getClassName', []);
    }
}
