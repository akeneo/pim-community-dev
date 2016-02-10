<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValue;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleRelationManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SmartViewUpdaterSpec extends ObjectBehavior
{
    function let(RuleRelationManager $ruleRelationManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->beConstructedWith($ruleRelationManager, $urlGenerator);
    }

    function it_is_a_form_view_updater()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterInterface');
    }

    function it_marks_an_attribute_as_impacted_by_a_rule($urlGenerator, $ruleRelationManager)
    {
        $formView = new FormView();
        $formView->vars['value'] = new ProductValue();

        $ruleRelationManager->isAttributeImpacted(11)->shouldBeCalled()->willReturn(true);

        $urlGenerator->generate('pimee_catalog_rule_rule_index', [
            'resourceId' => 11,
            'resourceName' => 'attribute',
        ])->shouldBeCalled();

        $this->update([
            'value' => $formView,
            'id' => 11,
        ]);
    }

    function it_marks_a_group_of_attribute_as_impacted_by_a_rule($urlGenerator, $ruleRelationManager)
    {
        $formView = new FormView();
        $formView->vars['value'] = new ProductValue();

        $ruleRelationManager->isAttributeImpacted(11)->shouldBeCalled()->willReturn(true);

        $urlGenerator->generate('pimee_catalog_rule_rule_index', [
            'resourceId' => 11,
            'resourceName' => 'attribute',
        ])->shouldBeCalled();

        $this->update([
            'values' => [
                'scope' => $formView,
            ],
            'id' => 11,
        ]);
    }

    function it_is_not_marked_because_it_is_not_impacted_by_the_rule($ruleRelationManager, $urlGenerator)
    {
        $formView = new FormView();
        $formView->vars['value'] = new ProductValue();

        $ruleRelationManager->isAttributeImpacted(11)->shouldBeCalled()->willReturn(false);

        $urlGenerator->generate('pimee_catalog_rule_rule_index', [
            'resourceId' => 11,
            'resourceName' => 'attribute',
        ])->shouldNotBeCalled();

        $this->update([
            'value' => $formView,
            'id' => 11,
        ]);
    }

    function it_is_not_marked_because_it_is_not_product_value($ruleRelationManager, $urlGenerator)
    {
        $formView = new FormView();
        $formView->vars['value'] = 'wrong value';

        $ruleRelationManager->isAttributeImpacted(11)->shouldBeCalled()->willReturn(true);

        $urlGenerator->generate('pimee_catalog_rule_rule_index', [
            'resourceId' => 11,
            'resourceName' => 'attribute',
        ])->shouldNotBeCalled();

        $this->update([
            'value' => $formView,
            'id' => 11,
        ]);
    }
}
