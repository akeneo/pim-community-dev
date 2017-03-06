<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Widget;

use PimEnterprise\Bundle\TeamworkAssistantBundle\Widget\ProjectCompletenessWidget;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;

class ProjectCompletenessWidgetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCompletenessWidget::class);
        $this->shouldImplement(WidgetInterface::class);
    }

    function it_has_data()
    {
        $this->getData()->shouldReturn([]);
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('project_progress');
    }

    function it_has_a_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseTeamworkAssistantBundle:Widget:completeness.html.twig');
    }

    function it_has_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }
}
