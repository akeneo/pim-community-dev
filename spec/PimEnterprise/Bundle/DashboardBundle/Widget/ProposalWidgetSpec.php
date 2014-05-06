<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;

class ProposalWidgetSpec extends ObjectBehavior
{
    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_proposal_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseDashboardBundle:Widget:proposals.html.twig');
    }

    function it_exposes_the_proposal_widget_template_parameters()
    {
        $this->getParameters()->shouldReturn(['params' => []]);
    }
}
