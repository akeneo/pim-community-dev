<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Applier;

use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductDraftApplierSpec extends ObjectBehavior
{
    function let(PropertySetterInterface $propertySetter, EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($propertySetter, $dispatcher);
    }

    function it_is_a_applier()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface');
    }

    function it_does_not_apply_a_draft_without_values(
        ProductInterface $product,
        ProductDraftInterface $productDraft,
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

    function it_applies_a_draft(
        ProductInterface $product,
        ProductDraftInterface $productDraft,
        $dispatcher,
        $propertySetter
    ) {
        $productDraft->getChanges()->willReturn(['values' =>
            [
                'name' => [
                    ['data' => 'Test', 'locale' => null, 'scope' => null]
                ],
                'description' => [
                    ['data' => 'Description EN ecommerce', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'Description EN print', 'locale' => 'en_US', 'scope' => 'print'],
                    ['data' => 'Description FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'Description FR print', 'locale' => 'fr_FR', 'scope' => 'print'],
                ]
            ]
        ]);
        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_APPLY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $propertySetter->setData($product, 'name', 'Test', [
            'locale' => null, 'scope' => null
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description EN ecommerce', [
            'locale' => 'en_US', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description EN print', [
            'locale' => 'en_US', 'scope' => 'print'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description FR ecommerce', [
            'locale' => 'fr_FR', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description FR print', [
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
