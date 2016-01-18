<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Prophecy\Argument;

class ProductAssociationWriterSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, CacheClearer $clearer, StepExecution $stepExecution)
    {
        $this->beConstructedWith($registry, $clearer);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\ProductAssociationWriter');
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_is_a_configurable_step_element()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
    }

    function it_increments_summary_info(
        $registry,
        $stepExecution,
        AssociationInterface $association1,
        AssociationInterface $association2,
        ProductInterface $product1,
        ProductInterface $product2,
        GroupInterface $group1,
        GroupInterface $group2,
        ObjectManager $manager
    ) {
        $registry->getManagerForClass(Argument::any())->willReturn($manager);
        $registry->getManagers()->willReturn([$manager]);

        $association1->getProducts()->willReturn([$product1, $product2]);
        $association1->getGroups()->willReturn([$group1, $group2]);
        $association1->getId()->willReturn(2);
        $association2->getProducts()->willReturn([$product1, $product2]);
        $association2->getGroups()->willReturn([$group1, $group2]);
        $association2->getId()->willReturn(null);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(4);
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalledTimes(4);

        $this->write([$association1, $association2]);
    }

    function it_throws_exception_when_receiving_anything_else_than_object()
    {
        $this->shouldThrow(new \InvalidArgumentException('Expecting item of type object, got "string"'))
            ->duringWrite(['myRawData']);
    }

    function it_saves_associations(
        $registry,
        AssociationInterface $association1,
        AssociationInterface $association2,
        ProductInterface $product1,
        ProductInterface $product2,
        GroupInterface $group1,
        GroupInterface $group2,
        ObjectManager $manager
    ) {
        $registry->getManagerForClass(Argument::any())->willReturn($manager);
        $registry->getManagers()->willReturn([$manager]);

        $association1->getProducts()->willReturn([$product1, $product2]);
        $association1->getGroups()->willReturn([$group1, $group2]);
        $association1->getId()->willReturn(2);
        $association2->getProducts()->willReturn([$product1, $product2]);
        $association2->getGroups()->willReturn([$group1, $group2]);
        $association2->getId()->willReturn(null);

        $manager->persist($association1)->shouldBeCalled();
        $manager->persist($association2)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->write([$association1, $association2]);
    }
}
