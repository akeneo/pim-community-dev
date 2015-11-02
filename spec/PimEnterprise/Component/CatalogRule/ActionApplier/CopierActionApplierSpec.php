<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class CopierActionApplierSpec extends ObjectBehavior
{
    function let(PropertyCopierInterface $propertyCopier)
    {
        $this->beConstructedWith($propertyCopier);
    }

    function it_supports_copy_action(ProductCopyValueActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_action_on_copier($propertyCopier, ProductCopyValueActionInterface $action, ProductInterface $product)
    {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getFromLocale()->willReturn(null);
        $action->getFromScope()->willReturn(null);
        $action->getToLocale()->willReturn(null);
        $action->getToScope()->willReturn(null);

        $propertyCopier->copyData(
            $product,
            $product,
            'sku',
            'name',
            [
                'from_locale' => null,
                'from_scope'  => null,
                'to_locale'   => null,
                'to_scope'    => null,
            ]
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }
}
