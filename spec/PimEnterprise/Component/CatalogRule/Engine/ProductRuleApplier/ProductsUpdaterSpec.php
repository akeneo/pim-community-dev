<?php

namespace spec\PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;
use Prophecy\Argument;

class ProductsUpdaterSpec extends ObjectBehavior
{
    function let(
        ActionApplierRegistryInterface $applierRegistry
    ) {
        $this->beConstructedWith($applierRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsUpdater');
    }

    function it_does_not_update_products_when_no_actions(
        $applierRegistry,
        RuleInterface $rule,
        ProductInterface $product
    ) {
        $rule->getActions()->willReturn([]);

        $applierRegistry->getActionApplier(Argument::any())->shouldNotBeCalled();

        $this->update($rule, [$product]);
    }

    function it_updates_product_when_the_rule_has_a_set_action(
        $applierRegistry,
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

        $this->update($rule, [$product]);
    }
}
