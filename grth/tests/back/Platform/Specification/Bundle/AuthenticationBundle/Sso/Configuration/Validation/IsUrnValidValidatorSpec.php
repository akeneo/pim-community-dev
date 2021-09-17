<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\IsUrnValid;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\IsUrnValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsUrnValidValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsUrnValidValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_passes_if_the_urn_is_valid($context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('urn:oasis:names:specification:docbook:dtd:xml:4.1.2', new IsUrnValid());
    }

    function it_adds_a_violation_if_the_urn_is_wrongly_formatted(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('This is not a valid URN.')
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('jambon', new IsUrnValid());
    }
}
