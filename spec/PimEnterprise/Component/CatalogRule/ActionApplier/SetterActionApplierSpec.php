<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;

class SetterActionApplierSpec extends ObjectBehavior
{
    function let(PropertySetterInterface $propertySetter)
    {
        $this->beConstructedWith($propertySetter);
    }

    function it_supports_set_action(ProductSetActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_action_on_copier($propertySetter, ProductSetActionInterface $action, ProductInterface $product)
    {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $propertySetter->setData(
            $product,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }
}
