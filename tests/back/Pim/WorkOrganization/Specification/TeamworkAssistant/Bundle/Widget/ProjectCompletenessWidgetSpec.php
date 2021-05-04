<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Widget;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Widget\ProjectCompletenessWidget;

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
        $this->getTemplate()->shouldReturn('');
    }

    function it_has_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }
}
