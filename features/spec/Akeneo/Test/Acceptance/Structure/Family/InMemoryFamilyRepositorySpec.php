<?php

namespace spec\Akeneo\Test\Acceptance\Structure\Family;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\Acceptance\Structure\Family\InMemoryFamilyRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;

class InMemoryFamilyRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryFamilyRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_is_a_family_repository()
    {
        $this->shouldBeAnInstanceOf(FamilyRepositoryInterface::class);
    }

    function it_asserts_the_identifier_property_is_the_code()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_family_by_identifier()
    {
        $family = new Family();
        $family->setCode('a-family');
        $this->beConstructedWith([$family->getCode() => $family]);

        $this->findOneByIdentifier('a-family')->shouldReturn($family);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-family')->shouldReturn(null);
    }

    function it_saves_a_family()
    {
        $family = new Family();
        $family->setCode('a-family');

        $this->save($family)->shouldReturn(null);

        $this->findOneByIdentifier($family->getCode())->shouldReturn($family);
    }

    function it_saves_only_families()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }

    function it_asserts_that_the_other_methods_are_not_implemented_yet()
    {
        $this->shouldThrow(NotImplementedException::class)->during('getFullRequirementsQB', [new Family(), 'en_US']);
        $this->shouldThrow(NotImplementedException::class)->during('getFullFamilies', [new Family(), new Channel()]);
        $this->shouldThrow(NotImplementedException::class)->during('findByIds', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('hasAttribute', ['a-family', 'an-attribute']);
        $this->shouldThrow(NotImplementedException::class)->during('find', ['a-family']);
        $this->shouldThrow(NotImplementedException::class)->during('findAll', []);
        $this->shouldThrow(NotImplementedException::class)->during('findBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('findOneBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('getClassName', []);
    }
}
