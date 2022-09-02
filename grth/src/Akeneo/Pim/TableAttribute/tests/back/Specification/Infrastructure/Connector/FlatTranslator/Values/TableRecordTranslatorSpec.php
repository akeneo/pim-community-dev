<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Akeneo\Pim\TableAttribute\Domain\Value\Query\GetRecordLabel;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableRecordTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableRecordTranslatorSpec extends ObjectBehavior
{
    function let(GetRecordLabel $getRecordLabel)
    {
        $this->beConstructedWith($getRecordLabel);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableRecordTranslator::class);
        $this->shouldImplement(TableValueTranslator::class);
    }

    function it_supports_select_columns()
    {
        $this->getSupportedColumnDataType()->shouldReturn(ReferenceEntityColumn::DATATYPE);
    }

    function it_translates_a_record(GetRecordLabel $getRecordLabel)
    {
        $columnDefinition = $this->getReferenceEntityColumn();

        $getRecordLabel
            ->__invoke(ReferenceEntityIdentifier::fromString('color'), 'red', 'fr_FR')
            ->willReturn('Rouge');

        $this->translate('nutrition', $columnDefinition, 'fr_FR', 'red')->shouldReturn('Rouge');
    }

    function it_returns_code_when_label_does_not_exist(GetRecordLabel $getRecordLabel)
    {
        $columnDefinition = $this->getReferenceEntityColumn();

        $getRecordLabel
            ->__invoke(ReferenceEntityIdentifier::fromString('color'), 'red', 'fr_FR')
            ->willReturn(null);

        $this->translate('nutrition', $columnDefinition, 'fr_FR', 'red')->shouldReturn('[red]');
    }

    function it_throws_an_exception_if_column_is_not_reference_entity()
    {
        $columnDefinition = BooleanColumn::fromNormalized(
            [
                'code' => 'columnYes',
                'labels' => [],
                'id' => ColumnIdGenerator::isAllergenic()
            ]
        );

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('translate',['nutrition', $columnDefinition, 'fr_FR', 'red']);
    }

    private function getReferenceEntityColumn(): ReferenceEntityColumn
    {
        return ReferenceEntityColumn::fromNormalized(
            [
                'code' => 'columnColor',
                'labels' => [],
                'id' => ColumnIdGenerator::record(),
                'reference_entity_identifier' => 'color'
            ]
        );
    }
}
