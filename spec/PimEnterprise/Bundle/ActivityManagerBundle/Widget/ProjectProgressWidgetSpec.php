<?php

namespace spec\Akeneo\ActivityManager\Bundle\Widget;

use Akeneo\ActivityManager\Bundle\Widget\ProjectProgressWidget;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;

class ProjectProgressWidgetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectProgressWidget::class);
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
        $this->getTemplate()->shouldReturn('ActivityManagerBundle:Widget:progress.html.twig');
    }

    function it_has_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }
}
