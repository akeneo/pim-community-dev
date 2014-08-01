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
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;

class ProductDraftManagerSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        ProductManager $manager,
        UserContext $userContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        ProductDraftChangesApplier $applier,
        EventDispatcherInterface $dispatcher
    ) {
        $this->beConstructedWith($registry, $manager, $userContext, $factory, $repository, $applier, $dispatcher);
    }

    function it_applies_changes_to_the_product_when_approving_a_proposition(
        $registry,
        $manager,
        $applier,
        $dispatcher,
        Proposition $productDraft,
        ProductInterface $product,
        ObjectManager $manager
    ) {
        $productDraft->getChanges()->willReturn(['foo' => 'bar', 'b' => 'c']);
        $productDraft->getProduct()->willReturn($product);
        $registry->getManagerForClass(get_class($productDraft->getWrappedObject()))->willReturn($manager);

        $dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, Argument::type('PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent'))->shouldBeCalled();
        $applier->apply($product, $productDraft)->shouldBeCalled();
        $manager->handleMedia($product)->shouldBeCalled();
        $manager->saveProduct($product, ['bypass_proposition' => true])->shouldBeCalled();
        $manager->remove($productDraft)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->approve($productDraft);
    }

    function it_marks_as_in_progress_proposition_which_is_ready_when_refusing_it(
        $registry,
        $dispatcher,
        Proposition $productDraft,
        ObjectManager $manager
    ) {
        $registry->getManagerForClass(get_class($productDraft->getWrappedObject()))->willReturn($manager);

        $productDraft->isInProgress()->willReturn(false);
        $dispatcher->dispatch(ProductDraftEvents::PRE_REFUSE, Argument::type('PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent'))->shouldBeCalled();
        $productDraft->setStatus(Proposition::IN_PROGRESS)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->refuse($productDraft);
    }
    function it_removes_in_progress_proposition_when_refusing_it(
        $registry,
        Proposition $productDraft,
        ObjectManager $manager
    ) {
        $registry->getManagerForClass(get_class($productDraft->getWrappedObject()))->willReturn($manager);

        $productDraft->isInProgress()->willReturn(true);
        $manager->remove($productDraft)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->refuse($productDraft);
    }

    function it_finds_a_proposition_when_it_already_exists(
        $userContext,
        $repository,
        UserInterface $user,
        ProductInterface $product,
        Proposition $productDraft
    ) {
        $user->getUsername()->willReturn('peter');
        $userContext->getUser()->willReturn($user);
        $repository->findUserProposition($product, 'peter')->willReturn($productDraft);

        $this->findOrCreate($product);
    }

    function it_creates_a_proposition_when_it_does_not_exist(
        $userContext,
        $repository,
        $factory,
        UserInterface $user,
        ProductInterface $product,
        Proposition $productDraft
    ) {
        $user->getUsername()->willReturn('peter');
        $userContext->getUser()->willReturn($user);
        $repository->findUserProposition($product, 'peter')->willReturn(null);
        $factory->createProposition($product, 'peter')->willReturn($productDraft);

        $this->findOrCreate($product)->shouldReturn($productDraft);
    }

    function it_throws_exception_when_find_proposition_and_current_cannot_be_resolved(
        $userContext,
        ProductInterface $product
    ) {
        $userContext->getUser()->willReturn(null);

        $this->shouldThrow(new \LogicException('Current user cannot be resolved'))->duringFindOrCreate($product, 'fr_FR');
    }

    function it_marks_proposition_as_ready(
        $registry,
        $dispatcher,
        Proposition $productDraft,
        ObjectManager $manager
    ) {
        $registry->getManagerForClass(get_class($productDraft->getWrappedObject()))->willReturn($manager);

        $dispatcher->dispatch(ProductDraftEvents::PRE_READY, Argument::type('PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent'))->shouldBeCalled();
        $productDraft->setStatus(Proposition::READY)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $this->markAsReady($productDraft);
    }
}
