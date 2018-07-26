<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectIdentifier;
use Prophecy\Argument;

class ProjectIdentifierSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('My label', 'print', 'en_US');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectIdentifier::class);
    }

    function it_returns_a_project_identifier()
    {
        $this->__toString()->shouldReturn('my-label-print-en-us');
    }
}
