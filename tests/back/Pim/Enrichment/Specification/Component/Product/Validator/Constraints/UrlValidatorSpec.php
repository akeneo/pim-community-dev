<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Url;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UrlValidator;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\UrlValidator as BaseUrlValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UrlValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UrlValidator::class);
    }

    public function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(BaseUrlValidator::class);
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    public function it_allows_null_value(
        ExecutionContextInterface $context,
    ): void {
        $constraint = new Url(['attributeCode' => 'a_code']);
        $context->getViolations()->willReturn(new ConstraintViolationList());
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    public function it_allows_empty_value(
        ExecutionContextInterface $context,
    ): void {
        $constraint = new Url(['attributeCode' => 'a_code']);
        $context->getViolations()->willReturn(new ConstraintViolationList());

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('', $constraint);
    }

    public function it_does_not_validate_a_bad_url(
        $context,
        ConstraintViolation $constraintViolation,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $badUrl = 'htp://bad.url';
        $constraint = new Url(['attributeCode' => 'a_code']);

        $context->buildViolation(Argument::any())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(Argument::any())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $constraintViolationList = new ConstraintViolationList([$constraintViolation->getWrappedObject()]);
        $context->getViolations()->willReturn($constraintViolationList);
        $constraintViolation->getCode()->willReturn(Url::INVALID_URL_ERROR);
        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%attribute%', $constraint->attributeCode)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setInvalidValue($badUrl)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setCode(Url::INVALID_URL_ERROR)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(2);
        $constraintViolationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($badUrl, $constraint);
    }

    public function it_throws_an_exception_if_the_constraint_is_not_an_url(): void
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['value', new IsString()]);
    }

    public function it_validates_a_good_url(ExecutionContextInterface $context): void
    {
        $goodUrl = 'https://www.akeneo.com';
        $constraint = new Url(['attributeCode' => 'a_code']);

        $context->getViolations()->willReturn([]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($goodUrl, $constraint);
    }
}
