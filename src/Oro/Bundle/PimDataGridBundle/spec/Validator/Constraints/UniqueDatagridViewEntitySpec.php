<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Oro\Bundle\PimDataGridBundle\Validator\Constraints\UniqueDatagridViewEntity;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class UniqueDatagridViewEntitySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueDatagridViewEntity::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_validated_by_a_specific_validator()
    {
        $this->validatedBy()->shouldReturn('pim_unique_datagrid_view_validator_entity');
    }

    function it_validates_a_class()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}
