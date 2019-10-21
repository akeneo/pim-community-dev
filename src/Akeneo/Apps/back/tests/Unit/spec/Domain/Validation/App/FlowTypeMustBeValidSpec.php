<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Validation\App;

use Akeneo\Apps\Domain\Model\Write\FlowType;
use Akeneo\Apps\Domain\Validation\App\FlowTypeMustBeValid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FlowTypeMustBeValidSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FlowTypeMustBeValid::class);
    }

    function it_does_not_build_violation_on_valid_flow_type(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(FlowType::DATA_DESTINATION, $context);
    }

    function it_adds_a_violation_when_the_flow_type_is_invalid(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('akeneo_apps.app.constraint.flow_type.invalid')->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('Unknown Flow Type', $context);
    }
}
