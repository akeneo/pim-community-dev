<?php

namespace spec\Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Test\Common\EntityWithValue\Association;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\AssociationType\InMemoryAssociationTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InMemoryAssociationTypeRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryAssociationTypeRepository::class);
    }

    function it_is_a_association_type_repository()
    {
        $this->shouldImplement(AssociationTypeRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_association_type()
    {
        $this->save(new AssociationType())->shouldReturn(null);
    }

    function it_only_saves_association_types()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_association_type_by_its_identifier()
    {
        $association = new AssociationType();
        $association->setCode('association_type_code');
        $this->save($association);
        $this->findOneByIdentifier('association_type_code')->shouldReturn($association);
    }

    function it_returns_null_if_the_association_type_does_not_exist()
    {
        $this->findOneByIdentifier('association_type_code')->shouldReturn(null);
    }

    function it_finds_missing_association_type()
    {
        $associationType = new AssociationType();
        $associationType->setCode('code');
        $productAssociation = new ProductAssociation();
        $productAssociation->setAssociationType($associationType);
        $productAddedToRepo = new Product();
        $productAddedToRepo->setAssociations(new ArrayCollection([$productAssociation]));

        $this->save($associationType);

        $this->findMissingAssociationTypes($productAddedToRepo)->shouldReturn([]);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }
}
