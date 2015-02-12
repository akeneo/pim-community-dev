<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssociationTypeManagerSpec extends ObjectBehavior
{
    function let(
        AssociationTypeRepositoryInterface $repository,
        ObjectManager $objectManager
    ) {
        $this->beConstructedWith($repository, $objectManager);
    }

    function it_provides_all_association_types($repository)
    {
        $repository->findAll()->willReturn(['foo', 'bar']);

        $this->getAssociationTypes()->shouldReturn(['foo', 'bar']);
    }
}
