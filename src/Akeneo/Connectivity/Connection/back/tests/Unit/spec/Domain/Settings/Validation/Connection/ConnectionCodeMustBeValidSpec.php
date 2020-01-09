<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection\ConnectionCodeMustBeValid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConnectionCodeMustBeValidSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ConnectionCodeMustBeValid::class);
    }

    public function it_does_not_build_violation_on_valid_code(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('magento', $context);
    }

    public function it_adds_a_violation_when_the_code_is_empty(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('akeneo_connectivity.connection.connection.constraint.code.required')->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('', $context);
    }
}
