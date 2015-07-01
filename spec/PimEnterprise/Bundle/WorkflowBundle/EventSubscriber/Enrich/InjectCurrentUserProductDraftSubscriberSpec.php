<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class InjectCurrentUserProductDraftSubscriberSpec extends ObjectBehavior
{
    function let(
        UserContext $userContext,
        CatalogContext $catalogContext,
        ProductDraftRepositoryInterface $repository,
        ProductDraftChangesApplier $applier
    ) {
        $this->beConstructedWith($userContext, $catalogContext, $repository, $applier);
    }

    function it_applies_product_changes_when_finding_one(
        $userContext,
        $repository,
        $applier,
        ProductInterface $product,
        UserInterface $user,
        ProductDraftInterface $productDraft,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $repository->findUserProductDraft($product, 'julia')->willReturn($productDraft);
        $applier->apply($product, $productDraft)->shouldBeCalled();

        $this->inject($event);
    }

    function it_applies_nothing_if_there_is_no_product(
        $applier,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn(null);
        $applier->apply(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->inject($event);
    }

    function it_applies_nothing_if_there_is_no_user(
        $userContext,
        $applier,
        ProductInterface $product,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $userContext->getUser()->willReturn(null);
        $applier->apply($product, Argument::any())->shouldNotBeCalled();

        $this->inject($event);
    }

    function it_applies_nothing_if_there_is_no_product_draft(
        $userContext,
        $repository,
        $applier,
        ProductInterface $product,
        UserInterface $user,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $repository->findUserProductDraft($product, 'julia')->willReturn(null);
        $applier->apply($product, Argument::any())->shouldNotBeCalled();

        $this->inject($event);
    }
}
