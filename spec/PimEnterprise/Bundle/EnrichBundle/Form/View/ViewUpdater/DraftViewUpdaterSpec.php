<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DraftViewUpdaterSpec extends ObjectBehavior
{
    function let(ProductDraftChangesApplier $applier, UrlGeneratorInterface $urlGenerator)
    {
        $this->beConstructedWith($applier, $urlGenerator);
    }

    function it_is_a_form_view_updater()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterInterface');
    }
}
