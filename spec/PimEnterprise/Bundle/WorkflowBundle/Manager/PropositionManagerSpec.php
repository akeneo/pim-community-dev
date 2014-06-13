<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

class PropositionManagerSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, ProductManager $manager, ProductChangesApplier $applier)
    {
        $this->beConstructedWith($registry, $manager, $applier);
    }

    function it_applies_changes_to_the_product_when_approving_a_proposition(
        $registry,
        $manager,
        $applier,
        Proposition $proposition,
        AbstractProduct $product,
        ObjectManager $manager
    ) {
        $proposition->getChanges()->willReturn(['foo' => 'bar', 'b' => 'c']);
        $proposition->getProduct()->willReturn($product);
        $registry->getManagerForClass(get_class($proposition->getWrappedObject()))->willReturn($manager);

        $applier->apply($product, ['foo' => 'bar', 'b' => 'c'])->shouldBeCalled();
        $manager->handleMedia($product)->shouldBeCalled();
        $manager->saveProduct($product, ['bypass_proposition' => true])->shouldBeCalled();
        $manager->remove($proposition)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->approve($proposition);
    }

    function it_closes_proposition_when_refusing_it(
        $registry,
        Proposition $proposition,
        ObjectManager $manager
    ) {
        $registry->getManagerForClass(get_class($proposition->getWrappedObject()))->willReturn($manager);

        $manager->remove($proposition)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->refuse($proposition);
    }
}
