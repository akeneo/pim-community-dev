<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Applier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdater;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DraftApplierSpec extends ObjectBehavior
{
    function let(ProductUpdater $productUpdater, EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($productUpdater, $dispatcher);
    }

    function it_is_a_applier()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Applier\ApplierInterface');
    }

    function it_does_not_apply_a_draft_without_values(
        ProductInterface $product,
        ProductDraft $productDraft,
        $dispatcher
    ) {
        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_APPLY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_APPLY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldNotBeCalled();

        $this->apply($product, $productDraft);
    }

    function it_applies_a_draft(ProductInterface $product, ProductDraft $productDraft, $dispatcher, $productUpdater)
    {
        $productDraft->getChanges()->willReturn(['values' =>
            [
                'name' => [
                    ['value' => 'Test', 'locale' => null, 'scope' => null]
                ],
                'description' => [
                    ['value' => 'Description EN ecommerce', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['value' => 'Description EN print', 'locale' => 'en_US', 'scope' => 'print'],
                    ['value' => 'Description FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['value' => 'Description FR print', 'locale' => 'fr_FR', 'scope' => 'print'],
                ]
            ]
        ]);
        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_APPLY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $productUpdater->setData($product, 'name', 'Test', [
            'locale' => null, 'scope' => null
        ])->shouldBeCalled();
        $productUpdater->setData($product, 'description', 'Description EN ecommerce', [
            'locale' => 'en_US', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $productUpdater->setData($product, 'description', 'Description EN print', [
            'locale' => 'en_US', 'scope' => 'print'
        ])->shouldBeCalled();
        $productUpdater->setData($product, 'description', 'Description FR ecommerce', [
            'locale' => 'fr_FR', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $productUpdater->setData($product, 'description', 'Description FR print', [
            'locale' => 'fr_FR', 'scope' => 'print'
        ])->shouldBeCalled();

        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_APPLY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->apply($product, $productDraft);
    }
}
