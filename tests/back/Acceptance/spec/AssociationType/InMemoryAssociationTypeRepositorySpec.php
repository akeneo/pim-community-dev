<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;

class InMemoryAssociationTypeRepositorySpec extends ObjectBehavior
{
    function it_is_an_association_type_repository()
    {
        $this->shouldImplement(AssociationTypeRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_returns_an_identifier_property()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_one_association_type_by_identifier()
    {
        $associationType = $this->createAssociationType('association_type_1');
        $this->beConstructedWith([$associationType->getCode() => $associationType]);

        $this->findOneByIdentifier('association_type_1')->shouldReturn($associationType);
    }

    function it_does_not_find_an_association_type_by_identifier()
    {
        $associationType = $this->createAssociationType('association_type_1');
        $this->beConstructedWith([$associationType->getCode() => $associationType]);

        $this->findOneByIdentifier('association_type_2')->shouldReturn(null);
    }

    function it_saves_an_associationType()
    {
        $associationType = $this->createAssociationType('association_type_1');
        $this->save($associationType);

        $this->findOneByIdentifier('association_type_1')->shouldReturn($associationType);
    }

    function it_throws_an_exception_if_saved_object_is_not_an_associationType(\StdClass $object)
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The object argument should be an association type'))
            ->during('save', [$object]);
    }

    private function createAssociationType(string $code): AssociationTypeInterface
    {
        $associationType = new AssociationType();
        $associationType->setCode($code);

        return $associationType;
    }
}
