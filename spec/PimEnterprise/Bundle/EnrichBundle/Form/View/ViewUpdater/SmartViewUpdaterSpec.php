<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SmartViewUpdaterSpec extends ObjectBehavior
{

    function let(RuleLinkedResourceManager $ruleLinkedResManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->beConstructedWith($ruleLinkedResManager, $urlGenerator);
    }

    function it_is_a_form_view_updater()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterInterface');
    }
}
