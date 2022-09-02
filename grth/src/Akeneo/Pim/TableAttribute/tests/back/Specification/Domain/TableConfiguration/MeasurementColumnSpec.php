<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementUnitCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class MeasurementColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'code' => 'duration',
                'labels' => ['en_US' => 'Measurement', 'fr_FR' => 'Mesure'],
                'id' => ColumnIdGenerator::duration(),
                'is_required_for_completeness' => true,
                'measurement_family_code' => 'duration_family',
                'measurement_default_unit_code' => 'second',
            ],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementColumn::class);
        $this->shouldImplement(ColumnDefinition::class);
    }

    function it_is_a_measurement_column()
    {
        $this->dataType()->shouldHaveType(ColumnDataType::class);
        $this->dataType()->asString()->shouldBe(MeasurementColumn::DATATYPE);
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('duration');
    }

    function it_has_an_id()
    {
        $this->id()->shouldHaveType(ColumnId::class);
        $this->id()->asString()->shouldBe(ColumnIdGenerator::duration());
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->normalize()->shouldReturn(['en_US' => 'Measurement', 'fr_FR' => 'Mesure']);
    }

    function it_is_required_for_completeness()
    {
        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(true);
    }

    function it_is_not_required_for_completeness()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'code' => 'duration',
                    'labels' => ['en_US' => 'Measurement', 'fr_FR' => 'Mesure'],
                    'id' => ColumnIdGenerator::duration(),
                    'is_required_for_completeness' => false,
                    'measurement_family_code' => 'duration_family',
                    'measurement_default_unit_code' => 'second',
                ],
            ]
        );

        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(false);
    }

    function it_has_a_measurement_family_code()
    {
        $this->measurementFamilyCode()->shouldHaveType(MeasurementFamilyCode::class);
        $this->measurementFamilyCode()->asString()->shouldReturn('duration_family');
    }

    function it_has_a_measurement_unit_code()
    {
        $this->measurementDefaultUnitCode()->shouldHaveType(MeasurementUnitCode::class);
        $this->measurementDefaultUnitCode()->asString()->shouldReturn('second');
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                'id' => ColumnIdGenerator::duration(),
                'code' => 'duration',
                'data_type' => MeasurementColumn::DATATYPE,
                'labels' => ['en_US' => 'Measurement', 'fr_FR' => 'Mesure'],
                'validations' => (object) [],
                'is_required_for_completeness' => true,
                'measurement_family_code' => 'duration_family',
                'measurement_default_unit_code' => 'second',
            ],
        );
    }
}
