<?php

namespace spec\Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Strategy;
use Gedmo\Tree\Strategy\ORM\Nested;
use Gedmo\Tree\TreeListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata,
        EventManager $eventManager,
        TreeListener $treeListener,
        Nested $strategy,
        \ReflectionProperty $property
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);

        $classMetadata->name = 'channel';
        $classMetadata->getReflectionProperty(Argument::any())->willReturn($property);

        $em->getConnection()->willReturn($connection);
        $em->getEventManager()->willReturn($eventManager);
        $em->getClassMetadata()->willReturn($classMetadata);

        $strategy->getName()->willReturn(Strategy::NESTED);
        $strategy->setNodePosition(Argument::cetera())->willReturn(null);

        $treeListener->getStrategy(Argument::cetera())->willReturn($strategy);

        $configuration = [
            'parent' => 'parent',
            'left'   => 'left'
        ];
        $treeListener->getConfiguration(Argument::cetera())->willReturn($configuration);

        $eventManager->getListeners()->willReturn([[$treeListener]]);

        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_nested_repository()
    {
        $this->shouldHaveType('Gedmo\Tree\Entity\Repository\NestedTreeRepository');
    }

    function it_is_a_category_repository()
    {
        $this->shouldImplement('Akeneo\Component\Classification\Repository\CategoryRepositoryInterface');
    }

    function it_is_an_identifiable_object_repository()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface');
    }
}
