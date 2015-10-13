<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class SetterActionApplierSpec extends ObjectBehavior
{
    function let(PropertySetterInterface $propertySetter)
    {
        $this->beConstructedWith($propertySetter);
    }

    function it_supports_set_action(ProductSetValueActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_action_on_copier($propertySetter, ProductSetValueActionInterface $action, ProductInterface $product)
    {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getLocale()->willReturn(null);
        $action->getScope()->willReturn(null);

        $propertySetter->setData(
            $product,
            'name',
            'sexy socks',
            [
                'locale' => null,
                'scope'  => null
            ]
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }
}
