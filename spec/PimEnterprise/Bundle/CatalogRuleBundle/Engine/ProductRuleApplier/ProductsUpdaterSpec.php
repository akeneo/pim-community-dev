<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
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
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater');
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
        ProductSetValueActionInterface $action,
        ActionApplierInterface $actionApplier
    ) {
        $action->getField()->willReturn('sku');
        $action->getValue()->willReturn('foo');
        $action->getScope()->willReturn('ecommerce');
        $action->getLocale()->willReturn('en_US');
        $rule->getActions()->willReturn([$action]);

        $applierRegistry->getActionApplier($action)->willReturn($actionApplier);
        $actionApplier->applyAction($action, [$product]);

        $templateUpdater->update(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->update($rule, [$product]);
    }

    function it_ensures_priority_of_variant_group_values_over_the_rule(
        $applierRegistry,
        $templateUpdater,
        RuleInterface $rule,
        ProductInterface $product,
        ProductCopyValueAction $action,
        GroupInterface $group,
        ProductTemplateInterface $productTemplate,
        ActionApplierInterface $actionApplier
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('description');
        $action->getFromLocale()->willReturn('fr_FR');
        $action->getToLocale()->willReturn('fr_CH');
        $action->getFromScope()->willReturn('ecommerce');
        $action->getToScope()->willReturn('tablet');
        $rule->getActions()->willReturn([$action]);

        $applierRegistry->getActionApplier($action)->willReturn($actionApplier);
        $actionApplier->applyAction($action, [$product]);

        $product->getVariantGroup()->willReturn($group);
        $group->getProductTemplate()->willReturn($productTemplate);
        $templateUpdater->update($productTemplate, [$product])
            ->shouldBeCalled();

        $this->update($rule, [$product]);
    }
}
