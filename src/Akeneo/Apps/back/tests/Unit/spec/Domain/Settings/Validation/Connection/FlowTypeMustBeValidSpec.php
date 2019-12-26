<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Settings\Validation\Connection;

use Akeneo\Apps\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Settings\Validation\Connection\FlowTypeMustBeValid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FlowTypeMustBeValidSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(FlowTypeMustBeValid::class);
    }

    public function it_does_not_build_violation_on_valid_flow_type(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(FlowType::DATA_DESTINATION, $context);
    }

    public function it_adds_a_violation_when_the_flow_type_is_invalid(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('akeneo_apps.connection.constraint.flow_type.invalid')->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('Unknown Flow Type', $context);
    }
}
