<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProposalPersister;

class ProposalManagerSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, ProposalPersister $persister, ProductChangesApplier $applier)
    {
        $this->beConstructedWith($registry, $persister, $applier);
    }

    function it_applies_changes_to_the_product_when_approving_a_proposal(
        $registry,
        $persister,
        $applier,
        Proposal $proposal,
        AbstractProduct $product,
        ObjectManager $manager
    ) {
        $proposal->getChanges()->willReturn(['foo' => 'bar', 'b' => 'c']);
        $proposal->getProduct()->willReturn($product);
        $registry->getManagerForClass(get_class($proposal->getWrappedObject()))->willReturn($manager);

        $applier->apply($product, ['foo' => 'bar', 'b' => 'c'])->shouldBeCalled();
        $persister->persist($product, ['bypass_proposal' => true])->shouldBeCalled();
        $proposal->setStatus(Proposal::APPROVED)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->approve($proposal);
    }

    function it_closes_proposal_when_refusing_it(
        $registry,
        Proposal $proposal,
        ObjectManager $manager
    ) {
        $registry->getManagerForClass(get_class($proposal->getWrappedObject()))->willReturn($manager);

        $proposal->setStatus(Proposal::REFUSED)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->refuse($proposal);
    }
}
