<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Date;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DateValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints\DateValidator as BaseDateValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(BaseDateValidator::class);
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    public function it_allows_null_value(
        ExecutionContextInterface $context,
    ): void {
        $constraint = new Date(['attributeCode' => 'a_code']);
        $context->getViolations()->willReturn(new ConstraintViolationList());
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    public function it_throws_an_exception_if_the_constraint_is_not_a_date(): void
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['value', new IsString()]);
    }

    public function it_validates_a_good_url(ExecutionContextInterface $context): void
    {
        $goodDate = '2021-02-01';
        $constraint = new Date(['attributeCode' => 'a_code']);

        $context->getViolations()->willReturn([]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($goodDate, $constraint);
    }

    public function it_does_not_validate_a_bad_date(
        $context,
        ConstraintViolation $constraintViolation,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $badDate = '2021/02-01';
        $constraint = new Date(['attributeCode' => 'a_code']);

        $context->buildViolation(Argument::any())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(Argument::any())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $constraintViolationList = new ConstraintViolationList([$constraintViolation->getWrappedObject()]);
        $context->getViolations()->willReturn($constraintViolationList);
        $constraintViolation->getCode()->willReturn(Date::INVALID_FORMAT_ERROR);
        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%attribute%', $constraint->attributeCode)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setInvalidValue($badDate)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setCode(Date::INVALID_FORMAT_ERROR)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(2);
        $constraintViolationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($badDate, $constraint);
    }
}
