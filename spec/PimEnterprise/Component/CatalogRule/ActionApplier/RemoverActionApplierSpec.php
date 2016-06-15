<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use PhpSpec\ObjectBehavior;
use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductRemoveActionInterface;

class RemoverActionApplierSpec extends ObjectBehavior
{
    function let(PropertyRemoverInterface $propertyRemover)
    {
        $this->beConstructedWith($propertyRemover);
    }

    function it_supports_remove_action(ProductRemoveActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_action_on_remover(
        $propertyRemover,
        ProductRemoveActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce'
        ]);
        $action->getItems()->willReturn([
            'multi1',
            'multi2'
        ]);

        $propertyRemover->removeData(
            $product,
            'multi-select',
            [
                'multi1',
                'multi2'
            ],
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce'
            ]
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

}
