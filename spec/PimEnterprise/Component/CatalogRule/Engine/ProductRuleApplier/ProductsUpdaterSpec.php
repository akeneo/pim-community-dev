<?php

namespace spec\PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;
use Prophecy\Argument;

class ProductsUpdaterSpec extends ObjectBehavior
{
    function let(
        ActionApplierRegistryInterface $applierRegistry,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->beConstructedWith($applierRegistry, $templateUpdater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsUpdater');
    }

    function it_does_not_update_products_when_no_actions(
        $applierRegistry,
        $templateUpdater,
        RuleInterface $rule,
        ProductInterface $product
    ) {
        $rule->getActions()->willReturn([]);

        $applierRegistry->getActionApplier(Argument::any())->shouldNotBeCalled();

        $templateUpdater->update(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->update($rule, [$product]);
    }

    function it_updates_product_when_the_rule_has_a_set_action(
        $applierRegistry,
        $templateUpdater,
        RuleInterface $rule,
        ProductInterface $product,
        ProductSetActionInterface $action,
        ActionApplierInterface $actionApplier
    ) {
        $action->getField()->willReturn('sku');
        $action->getValue()->willReturn('foo');
        $rule->getActions()->willReturn([$action]);

        $applierRegistry->getActionApplier($action)->willReturn($actionApplier);
        $actionApplier->applyAction($action, [$product]);

        $templateUpdater->update(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->update($rule, [$product]);
    }
}
