<?php                                                                           

namespace spec\Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferencedCollectionSpec extends ObjectBehavior
{
    protected $criteria;

    function let (
        ObjectManager $objectManager,
        ObjectRepository $repository,
        ClassMetadata $classMetadata
    ) {
        $itemIds = array(1, 2, 3);
        $itemClass = 'MyItemClass';
        $identifier = 'id';
        $this->criteria = array( $identifier => $itemIds);

        $repository->findBy($this->criteria)->willReturn(array())->shouldBeCalled();

        $classMetadata->getIdentifier()->willReturn($identifier);

        $objectManager->getRepository($itemClass)->willReturn($repository);
        $objectManager->getClassMetadata($itemClass)->willReturn($classMetadata);

        $this->beConstructedWith("MyItemClass", $itemIds, $objectManager);
    }

    function it_initializes()
    {
        $this->isInitialized()->shouldReturn(false);
        $this->initialize();
        $this->isInitialized()->shouldReturn(true);
    }

    function it_sets_initialized(ObjectRepository $repository)
    {
        $repository->findBy($this->criteria)->shouldNotBeCalled();
        $this->isInitialized()->shouldReturn(false);
        $this->setInitialized(true);
        $this->isInitialized()->shouldReturn(true);
    }

    function it_counts()
    {
        $this->count();
    }
}

