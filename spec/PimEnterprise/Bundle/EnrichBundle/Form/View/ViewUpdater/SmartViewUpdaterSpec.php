<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleRelationManager;
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
}
