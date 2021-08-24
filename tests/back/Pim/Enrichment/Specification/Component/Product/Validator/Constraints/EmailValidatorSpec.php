<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Email;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\EmailValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Email as BaseEmail;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmailValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->beConstructedWith();
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EmailValidator::class);
    }

    function it_does_not_validate_base_email_constraint(BaseEmail $constraint)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during(
            'validate',
            [
                'an_email',
                $constraint,
            ]
        );
    }

    function it_does_not_validate_an_incorrect_email(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ConstraintViolation $constraintViolation
    ) {
        $badEmail = 'bad_email';
        $constraint = new Email(['attributeCode' => 'a_code']);

        $context->buildViolation(Argument::any())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(Argument::any())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $constraintViolationList = new ConstraintViolationList([$constraintViolation->getWrappedObject()]);

        $context->getViolations()
            ->willReturn($constraintViolationList);
        $constraintViolation->getCode()
            ->willReturn(Email::INVALID_FORMAT_ERROR);
        $context->buildViolation($constraint->message)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%attribute%', $constraint->attributeCode)
            ->willReturn($constraintViolationBuilder)
            ->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setInvalidValue($badEmail)
            ->willReturn($constraintViolationBuilder)
            ->shouldBeCalledTimes(1);
        $constraintViolationBuilder->setCode(Email::INVALID_FORMAT_ERROR)
            ->willReturn($constraintViolationBuilder)
            ->shouldBeCalledTimes(2);
        $constraintViolationBuilder->addViolation()
            ->shouldBeCalledTimes(2);

        $this->validate($badEmail, $constraint);
    }

    function it_validates_good_email_field(ExecutionContextInterface $context)
    {
        $goodEmail = 'good@email.com';
        $constraint = new Email(['attributeCode' => 'a_code']);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $context->getViolations()->willReturn([]);

        $this->validate($goodEmail, $constraint);
    }
}
