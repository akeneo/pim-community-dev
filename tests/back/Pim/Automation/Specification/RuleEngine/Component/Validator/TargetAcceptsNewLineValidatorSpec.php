<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ConcatenateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\TargetAcceptsNewLine;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\TargetAcceptsNewLineValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TargetAcceptsNewLineValidatorSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(TargetAcceptsNewLineValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new ConcatenateAction([]), new IsNull()]);
    }

    function it_throws_an_exception_if_value_is_not_a_concatenate_action()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new TargetAcceptsNewLine()]);
    }

    function it_does_nothing_when_target_is_malformed(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $constraint = new TargetAcceptsNewLine();
        $data = [
            'from' => [
                ['field' => 'foo'],
                ['text' => 'bar'],
            ],
            'to' => ['locale' => 'en_US'],
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new ConcatenateAction($data), $constraint);
    }

    function it_adds_a_violation_when_target_attribute_is_text_and_a_new_line_is_provided(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new TargetAcceptsNewLine();
        $data = [
            'from' => [
                ['field' => 'foo'],
                ['text' => 'bar'],
                ['new_line' => null],
            ],
            'to' => ['field' => 'name'],
        ];
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );

        $context->buildViolation(
            $constraint->message,
            ['{{ targetField }}' => 'name']
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('from[2].new_line')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new ConcatenateAction($data), $constraint);
    }

    function it_does_not_add_a_violation_when_target_attribute_is_text_and_no_new_line_is_provided(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $constraint = new TargetAcceptsNewLine();
        $data = [
            'from' => [
                ['field' => 'foo'],
                ['text' => 'bar'],
            ],
            'to' => ['field' => 'name'],
        ];
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new ConcatenateAction($data), $constraint);
    }

    function it_does_not_add_a_violation_when_target_attribute_is_textarea(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $constraint = new TargetAcceptsNewLine();
        $data = [
            'from' => [
                ['field' => 'foo'],
                ['text' => 'bar'],
                ['new_line' => null]
            ],
            'to' => ['field' => 'name'],
        ];
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_textarea', [], false, false, null, null, null, 'string', [])
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new ConcatenateAction($data), $constraint);
    }
}
