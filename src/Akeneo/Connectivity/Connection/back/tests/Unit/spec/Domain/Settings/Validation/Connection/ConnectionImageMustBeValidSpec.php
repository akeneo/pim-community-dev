<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection\ConnectionImageMustBeValid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConnectionImageMustBeValidSpec extends ObjectBehavior
{
    public function it_is_a_connection_image_validator(): void
    {
        $this->shouldHaveType(ConnectionImageMustBeValid::class);
    }

    public function it_does_not_build_a_violation_if_the_image_is_valid(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('a/b/c/path.jpg', $context);
    }

    public function it_does_not_build_a_violation_if_the_image_is_null(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(null, $context);
    }

    public function it_builds_a_violation_if_image_is_not_valid(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('akeneo_connectivity.connection.connection.constraint.image.not_empty')->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('', $context);
    }
}
