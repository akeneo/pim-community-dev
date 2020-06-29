<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LengthValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LengthValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LengthValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(LengthValidator::class);
    }

    public function it_allows_null_value($context): void
    {
        $constraint = new Length(['max' => 5, 'attributeCode' => 'a_code']);
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    public function it_allows_empty_value($context): void
    {
        $constraint = new Length(['max' => 5, 'attributeCode' => 'a_code']);
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('', $constraint);
    }

    public function it_does_not_validate_a_too_long_string(
        $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $constraint = new Length(['max' => 5, 'attributeCode' => 'a_code']);
        $context
            ->buildViolation($constraint->maxMessage)
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder->setParameter('%attribute%', $constraint->attributeCode)
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%limit%', $constraint->max)
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setInvalidValue('azertyu')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setPlural((int) $constraint->max)
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(Length::TOO_LONG_ERROR)
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);

        $this->validate('azertyu', $constraint);
    }
    public function it_throws_an_exception_if_the_constraint_is_not_a_length(): void
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['value', new IsString()]);
    }
}
