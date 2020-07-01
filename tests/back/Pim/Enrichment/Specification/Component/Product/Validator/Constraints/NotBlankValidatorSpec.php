<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlank;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlankValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotBlankValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotBlankValidator::class);
    }

    function it_validates_string($context)
    {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('red', new NotBlank(['attributeCode' => 'color']));
    }

    function it_builds_a_violation_when_the_value_is_an_empty_string(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $constraint = new NotBlank(['attributeCode' => 'color']);

        $context
            ->buildViolation($constraint->message)
            ->willReturn($builder);

        $builder->setParameter('{{ value }}', '""')
            ->willReturn($builder);
        $builder->setParameter('{{ attribute_code }}', 'color')
            ->willReturn($builder);
        $builder->setCode(NotBlank::IS_BLANK_ERROR)
            ->willReturn($builder);

        $builder->addViolation()->shouldBeCalled();

        $this->validate('', $constraint);
    }
}
