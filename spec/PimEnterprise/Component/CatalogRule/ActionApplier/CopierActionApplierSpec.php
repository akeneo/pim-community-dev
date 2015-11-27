<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class CopierActionApplierSpec extends ObjectBehavior
{
    function let(PropertyCopierInterface $propertyCopier)
    {
        $this->beConstructedWith($propertyCopier);
    }

    function it_supports_copy_action(ProductCopyActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_action_on_copier($propertyCopier, ProductCopyActionInterface $action, ProductInterface $product)
    {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $propertyCopier->copyData(
            $product,
            $product,
            'sku',
            'name',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }
}
