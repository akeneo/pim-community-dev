<?php

namespace spec\Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Strategy\ORM\Nested;
use Gedmo\Tree\Strategy;
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

    function it_is_a_category_repository()
    {
        $this->shouldImplement('Akeneo\Component\Classification\Repository\CategoryRepositoryInterface');
    }

    function it_commits_data_when_moving_node_as_next_sibling(
        CategoryInterface $node,
        CategoryInterface $prevSibling,
        $em
    ) {
        $em->persist($node)->shouldBeCalled();
        $em->flush($node)->shouldBeCalled();
        $this->persistAsNextSiblingOf($node, $prevSibling);
    }

    function it_commits_data_when_moving_node_as_first_child(
        CategoryInterface $node,
        CategoryInterface $parent,
        $em
    ) {
        $em->persist($node)->shouldBeCalled();
        $em->flush($node)->shouldBeCalled();
        $this->persistAsFirstChildOf($node, $parent);
    }
}
