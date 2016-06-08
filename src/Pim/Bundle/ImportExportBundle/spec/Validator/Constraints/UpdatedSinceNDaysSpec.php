<?php

namespace spec\Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class UpdatedSinceNDaysSpec extends ObjectBehavior
{
    function let(JobInstance $jobInstance)
    {
        $this->beConstructedWith($jobInstance);
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Validator\Constraints\UpdatedSinceNDays');
    }

    function it_is_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }

    function it_has_required_options()
    {
        $this->getDefaultOption()->shouldReturn('jobInstance');
    }

    function it_has_targets()
    {
        $this->getTargets()->shouldReturn(Constraint::PROPERTY_CONSTRAINT);
    }

    function it_is_validated_by()
    {
        $this->validatedBy()->shouldReturn('updated_since_strategy');
    }
}
