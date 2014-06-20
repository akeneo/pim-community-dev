<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\PropositionChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PropositionFactory;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

class PropositionManagerSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        ProductManager $manager,
        UserContext $userContext,
        PropositionFactory $factory,
        PropositionRepositoryInterface $repository,
        PropositionChangesApplier $applier
    ) {
        $this->beConstructedWith($registry, $manager, $userContext, $factory, $repository, $applier);
    }

    function it_applies_changes_to_the_product_when_approving_a_proposition(
        $registry,
        $manager,
        $applier,
        Proposition $proposition,
        ProductInterface $product,
        ObjectManager $manager
    ) {
        $proposition->getChanges()->willReturn(['foo' => 'bar', 'b' => 'c']);
        $proposition->getProduct()->willReturn($product);
        $registry->getManagerForClass(get_class($proposition->getWrappedObject()))->willReturn($manager);

        $applier->apply($product, $proposition)->shouldBeCalled();
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

    function it_finds_a_proposition_when_it_already_exists(
        $userContext,
        $repository,
        UserInterface $user,
        ProductInterface $product,
        Proposition $proposition
    ) {
        $user->getUsername()->willReturn('peter');
        $userContext->getUser()->willReturn($user);
        $repository->findUserProposition($product, 'peter', 'fr_FR')->willReturn($proposition);

        $this->findOrCreate($product, 'fr_FR');
    }

    function it_creates_a_proposition_when_it_does_not_exist(
        $userContext,
        $repository,
        $factory,
        UserInterface $user,
        ProductInterface $product,
        Proposition $proposition
    ) {
        $user->getUsername()->willReturn('peter');
        $userContext->getUser()->willReturn($user);
        $repository->findUserProposition($product, 'peter', 'fr_FR')->willReturn(null);
        $factory->createProposition($product, 'peter', 'fr_FR')->willReturn($proposition);

        $this->findOrCreate($product, 'fr_FR')->shouldReturn($proposition);
    }

    function it_throws_exception_when_find_proposition_and_current_cannot_be_resolved(
        $userContext,
        ProductInterface $product
    ) {
        $userContext->getUser()->willReturn(null);

        $this->shouldThrow(new \LogicException('Current user cannot be resolved'))->duringFindOrCreate($product, 'fr_FR');
    }
}
