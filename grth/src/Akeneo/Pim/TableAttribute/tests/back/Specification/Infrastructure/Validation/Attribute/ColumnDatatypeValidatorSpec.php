<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ColumnDatatype;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ColumnDatatypeValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ColumnDatatypeValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $executionContext)
    {
        $this->beConstructedWith($this->getAllowedDatatypes());
        $this->initialize($executionContext);
    }

    public function getAllowedDatatypes(): array
    {
        return [ "text", "number", "boolean", "select" ];
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ColumnDatatypeValidator::class);
    }

    function it_does_nothing_when_datatype_is_valid(ExecutionContextInterface $executionContext)
    {
        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate("number", new ColumnDatatype());
    }

    function it_does_nothing_when_datatype_is_not_a_string(ExecutionContextInterface $executionContext)
    {
        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(['toto'], new ColumnDatatype());
    }

    function it_adds_a_violation_when_datatype_is_invalid(
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $executionContext->buildViolation(
            Argument::type('string'),
            [
                '{{ allowed_data_types }}' => implode(', ', $this->getAllowedDatatypes())
            ]
        )->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('column')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate("unknown", new ColumnDatatype());
    }
}
