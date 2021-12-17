<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableBooleanTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValueTranslator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PhpSpec\ObjectBehavior;

class TableBooleanTranslatorSpec extends ObjectBehavior
{
    function let(LabelTranslatorInterface $labelTranslator)
    {
        $labelTranslator->translate(
            'pim_common.yes',
            'en_US',
            \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'yes')
        )->willReturn('Yes');
        $labelTranslator->translate(
            'pim_common.no',
            'en_US',
            \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'no')
        )->willReturn('No');
        $labelTranslator->translate(
            'pim_common.yes',
            'fr_FR',
            \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'yes')
        )->willReturn('Oui');
        $labelTranslator->translate(
            'pim_common.no',
            'fr_FR',
            \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'no')
        )->willReturn('Non');

        $this->beConstructedWith($labelTranslator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableBooleanTranslator::class);
        $this->shouldImplement(TableValueTranslator::class);
    }

    function it_supports_boolean_columns()
    {
        $this->getSupportedColumnDataType()->shouldReturn(BooleanColumn::DATATYPE);
    }

    function it_translates_true_value()
    {
        $booleanColumn = BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']);

        $this->translate('nutrition', $booleanColumn, 'en_US', true)->shouldReturn('Yes');
        $this->translate('nutrition', $booleanColumn, 'fr_FR', true)->shouldReturn('Oui');
    }

    function it_translates_false_value()
    {
        $booleanColumn = BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']);

        $this->translate('nutrition', $booleanColumn, 'en_US', false)->shouldReturn('No');
        $this->translate('nutrition', $booleanColumn, 'fr_FR', false)->shouldReturn('Non');
    }

    function it_translates_unknown_value()
    {
        $booleanColumn = BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']);

        $this->translate('nutrition', $booleanColumn, 'en_US', 'unknown')->shouldReturn('[unknown]');
    }
}
