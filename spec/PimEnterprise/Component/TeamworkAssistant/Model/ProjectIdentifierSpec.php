<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Model;

use PimEnterprise\Component\TeamworkAssistant\Model\ProjectIdentifier;
use PhpSpec\ObjectBehavior;
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
