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
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyUsedInATableAttribute;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyUsedInATableAttributeValidator;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepository;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MeasurementFamilyUsedInATableAttributeValidatorSpec extends ObjectBehavior
{
    function let(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        IsMeasurementFamilyLinkedToATableColumn $isMeasurementFamilyLinkedToATableColumn,
        ExecutionContextInterface $context
    ) {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('duration'),
            LabelCollection::fromArray([]),
            UnitCode::fromString('second'),
            [
                Unit::create(
                    UnitCode::fromString('second'),
                    LabelCollection::fromArray([]),
                    [Operation::create('mul', '1')],
                    's'
                ),
                Unit::create(
                    UnitCode::fromString('minute'),
                    LabelCollection::fromArray([]),
                    [Operation::create('mul', '60')],
                    'min'
                ),
            ]
        );

        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('duration'))
            ->willReturn($measurementFamily);

        $isMeasurementFamilyLinkedToATableColumn->forCode('duration')->willReturn(true);

        $this->beConstructedWith($measurementFamilyRepository, $isMeasurementFamilyLinkedToATableColumn);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MeasurementFamilyUsedInATableAttributeValidator::class);
    }

    function it_fails_with_bad_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [
                new SaveMeasurementFamilyCommand('duration'),
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
                new MeasurementFamilyUsedInATableAttribute()
            ]
        );
    }

    function it_does_not_add_violation_when_measurement_family_does_not_exist(
        IsMeasurementFamilyLinkedToATableColumn $isMeasurementFamilyLinkedToATableColumn,
        MeasurementFamilyRepository $measurementFamilyRepository,
        ExecutionContext $context
    ) {
        $familyCode = 'unknown';

        $command = new SaveMeasurementFamilyCommand();
        $command->code = $familyCode;
        $command->standardUnitCode = 'meter_unit';
        $command->labels = [];
        $command->units = [];

        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($familyCode))
            ->shouldBeCalled()->willThrow(new MeasurementFamilyNotFoundException());

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            $command,
            new MeasurementFamilyUsedInATableAttribute()
        );
    }

    function it_does_not_add_violation_when_measurement_family_is_not_linked_to_a_table_column(
        ExecutionContext $context,
        IsMeasurementFamilyLinkedToATableColumn $isMeasurementFamilyLinkedToATableColumn
    ) {
        $command = new SaveMeasurementFamilyCommand();
        $command->code = 'duration';
        $command->standardUnitCode = 'second';
        $command->labels = [];
        $command->units = [];

        $isMeasurementFamilyLinkedToATableColumn->forCode('duration')->willReturn(false);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            $command,
            new MeasurementFamilyUsedInATableAttribute()
        );
    }

    function it_does_not_add_violation_when_no_unit_is_removed_and_no_conversion_operation_is_updated(
        IsMeasurementFamilyLinkedToATableColumn $isMeasurementFamilyLinkedToATableColumn,
        MeasurementFamilyRepository $measurementFamilyRepository,
        MeasurementFamily $measurementFamily,
        ExecutionContext $context
    ) {
        $command = new SaveMeasurementFamilyCommand();
        $command->code = 'duration';
        $command->standardUnitCode = 'second';
        $command->labels = ['en_US' => 'Duration'];
        $command->units = [
            [
                'code' => 'second',
                'labels' => ['en_US' => 'Second'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'sec',
            ],
            [
                'code' => 'minute',
                'labels' => ['en_US' => 'Minute', 'fr_FR' => 'Minute'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '60']],
                'symbol' => 'm',
            ],
            [
                'code' => 'hour',
                'labels' => ['en_US' => 'Hour', 'fr_FR' => 'Heure'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '3600']],
                'symbol' => 'h',
            ],
        ];

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            $command,
            new MeasurementFamilyUsedInATableAttribute()
        );
    }

    function it_adds_violation_when_trying_to_remove_a_unit(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $command = new SaveMeasurementFamilyCommand();
        $command->code = 'duration';
        $command->standardUnitCode = 'second';
        $command->labels = ['en_US' => 'Duration'];
        $command->units = [
            [
                'code' => 'second',
                'labels' => ['en_US' => 'Second'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'sec',
            ],
            [
                'code' => 'hour',
                'labels' => ['en_US' => 'Hour', 'fr_FR' => 'Heure'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '3600']],
                'symbol' => 'h',
            ],
        ];

        $context->buildViolation(Argument::type('string'))->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('units')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            $command,
            new MeasurementFamilyUsedInATableAttribute()
        );
    }

    function it_adds_a_violation_when_trying_to_update_an_conversion_operation(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $command = new SaveMeasurementFamilyCommand();
        $command->code = 'duration';
        $command->standardUnitCode = 'second';
        $command->labels = ['en_US' => 'Duration'];
        $command->units = [
            [
                'code' => 'second',
                'labels' => ['en_US' => 'Second'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'sec',
            ],
            [
                'code' => 'minute',
                'labels' => ['en_US' => 'Minute', 'fr_FR' => 'Minute'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '60'], ['operator' => 'add', 'value' => '5']],
                'symbol' => 'm',
            ],
            [
                'code' => 'hour',
                'labels' => ['en_US' => 'Hour', 'fr_FR' => 'Heure'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '3600']],
                'symbol' => 'h',
            ],
        ];

        $context->buildViolation(Argument::type('string'))->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('units')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            $command,
            new MeasurementFamilyUsedInATableAttribute()
        );
    }
}
