<?php                                                                           

namespace spec\Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferencedCollectionSpec extends ObjectBehavior
{
    protected $itemIds;

    function let (ObjectManager $objectManager, ObjectRepository $repository)
    {
        $this->itemIds = array(1, 2, 3);

        $repository->findBy($this->itemIds)->willReturn(array());
        $repository->findBy($this->itemIds)->shouldBeCalled();
        $objectManager->getRepository('MyItemClass')->willReturn($repository);
        $this->beConstructedWith("MyItemClass", $this->itemIds, $objectManager);
    }

    function it_initializes()
    {
        $this->isInitialized()->shouldReturn(false);
        $this->initialize();
        $this->isInitialized()->shouldReturn(true);
    }

    function it_sets_initialized(ObjectRepository $repository)
    {
        $repository->findBy($this->itemIds)->shouldNotBeCalled();
        $this->isInitialized()->shouldReturn(false);
        $this->setInitialized(true);
        $this->isInitialized()->shouldReturn(true);
    }

    function it_counts()
    {
        $this->count();
    }
}

