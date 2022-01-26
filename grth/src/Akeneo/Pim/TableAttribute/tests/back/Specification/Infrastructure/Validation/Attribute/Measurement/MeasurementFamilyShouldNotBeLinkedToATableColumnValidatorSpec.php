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

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsMeasurementFamilyLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyShouldNotBeLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyShouldNotBeLinkedToATableColumnValidator;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyCommand;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MeasurementFamilyShouldNotBeLinkedToATableColumnValidatorSpec extends ObjectBehavior
{
    function let(
        IsMeasurementFamilyLinkedToATableColumn $isMeasurementFamilyLinkedToATableColumn,
        ExecutionContextInterface $context
    )
    {
        $isMeasurementFamilyLinkedToATableColumn->forCode('duration')->willReturn(true);
        $isMeasurementFamilyLinkedToATableColumn->forCode('distance')->willReturn(false);

        $this->beConstructedWith($isMeasurementFamilyLinkedToATableColumn);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MeasurementFamilyShouldNotBeLinkedToATableColumnValidator::class);
    }

    function it_fails_with_bad_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [
                new DeleteMeasurementFamilyCommand('duration'),
                new Type('string')
            ]
        );
    }

    function it_fails_with_bad_command()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [
                new \stdClass(),
                new MeasurementFamilyShouldNotBeLinkedToATableColumn()
            ]
        );
    }

    function it_does_not_add_violation_when_measurement_family_is_not_linked_to_a_table_column(
        ExecutionContext $context
    )
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $command = new DeleteMeasurementFamilyCommand();
        $command->code = 'distance';

        $this->validate(
            $command,
            new MeasurementFamilyShouldNotBeLinkedToATableColumn()
        );
    }

    function it_adds_a_violation_when_measurement_family_is_linked_to_a_table_column(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    )
    {
        $context->buildViolation(Argument::any())->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $command = new DeleteMeasurementFamilyCommand();
        $command->code = 'duration';

        $this->validate(
            $command,
            new MeasurementFamilyShouldNotBeLinkedToATableColumn()
        );
    }
}
