<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Url;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UrlValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\UrlValidator as BaseUrlValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
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
        ConstraintViolationListInterface $constraintViolationList
    ): void
    {
        $constraint = new Url(['attributeCode' => 'a_code']);
        $context->getViolations()->willReturn($constraintViolationList);
        $constraintViolationList->count()->willreturn(0);
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    public function it_allows_empty_value(
        ExecutionContextInterface $context,
        ConstraintViolationListInterface $constraintViolationList
    ): void
    {
        $constraint = new Url(['attributeCode' => 'a_code']);
        $context->getViolations()->willReturn($constraintViolationList);
        $constraintViolationList->count()->willreturn(0);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('', $constraint);
    }

    public function it_does_not_validate_a_bad_url(
        ExecutionContextInterface $context,
        ConstraintViolationListInterface $constraintViolationList,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ConstraintViolationInterface $violation
    ): void
    {
        $constraint = new Url(['attributeCode' => 'a_code']);

        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ value }}', Argument::any())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(Url::INVALID_URL_ERROR)->willReturn($constraintViolationList);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $context->getViolations()->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(1)->shouldBeCalledTimes(1);
        $constraintViolationList->rewind()->willReturn($violation);
        $constraintViolationList->valid()->willReturn(true, false);
        $constraintViolationList->current()->willReturn($violation);
        $constraintViolationList->key()->willReturn('propertyPath');
        $violation->getCode()->willReturn(Url::INVALID_URL_ERROR);

        $constraintViolationList->remove('propertyPath')->shouldBeCalledTimes(1);

        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%attribute%', $constraint->attributeCode)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setInvalidValue('htp://bad.url')
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setCode(Url::INVALID_URL_ERROR)
            ->willReturn($constraintViolationBuilder)->shouldBeCalledTimes(2);
        $constraintViolationBuilder->addViolation()->shouldBeCalledTimes(2);
        $constraintViolationList->next()->willReturn(null);

        $this->validate('htp://bad.url', $constraint);
    }

    public function it_throws_an_exception_if_the_constraint_is_not_an_url(): void
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['value', new IsString()]);
    }
}
