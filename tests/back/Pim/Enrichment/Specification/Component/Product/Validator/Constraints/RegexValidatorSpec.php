<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Regex;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\RegexValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RegexValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RegexValidator::class);
    }

    function it_validates_null($context)
    {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, new Regex(['pattern' => '/^[0-9]+$/', 'attributeCode' => 'color']));
    }

    function it_validates_an_empty_string($context)
    {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('', new Regex(['pattern' => '/^[0-9]+$/', 'attributeCode' => 'color']));
    }

    function it_validates_a_value_that_match_the_pattern($context)
    {
        $constraint = new Regex(['pattern' => '/^[0-9]+$/', 'attributeCode' => 'color']);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('123', $constraint);
        $this->validate(123, $constraint);
    }

    function it_throws_if_the_value_can_not_be_converted_to_string()
    {
        $constraint = new Regex(['pattern' => '/^[0-9]+$/', 'attributeCode' => 'color']);

        $this
            ->shouldThrow(UnexpectedValueException::class)
            ->during('validate', [new \stdClass(), $constraint]);
    }

    function it_builds_a_violation_when_the_value_does_not_match_the_pattern(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $constraint = new Regex(['pattern' => '/^[0-9]+$/', 'attributeCode' => 'color']);

        $context
            ->buildViolation($constraint->message)
            ->willReturn($builder);

        $builder->setParameter('{{ value }}', '"abc"')
            ->willReturn($builder);
        $builder->setParameter('{{ pattern }}', '/^[0-9]+$/')
            ->willReturn($builder);
        $builder->setParameter('{{ attribute_code }}', 'color')
            ->willReturn($builder);
        $builder->setCode(Regex::REGEX_FAILED_ERROR)
            ->willReturn($builder);

        $builder->addViolation()->shouldBeCalled();

        $this->validate('abc', $constraint);
    }
}
