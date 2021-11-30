<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableSelectTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValueTranslator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableSelectTranslatorSpec extends ObjectBehavior
{
    function let(SelectOptionCollectionRepository $selectOptionCollectionRepository)
    {
        $selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredient'))->willReturn(
            SelectOptionCollection::fromNormalized([
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
                ['code' => 'pepper'],
            ])
        );
        $this->beConstructedWith($selectOptionCollectionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableSelectTranslator::class);
        $this->shouldImplement(TableValueTranslator::class);
    }

    function it_supports_select_columns()
    {
        $this->getSupportedColumnDataType()->shouldReturn(SelectColumn::DATATYPE);
    }

    function it_translates_options()
    {
        $selectColumn = SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']);

        $this->translate('nutrition', $selectColumn, 'en_US', 'salt')->shouldReturn('Salt');
        $this->translate('nutrition', $selectColumn, 'fr_FR', 'salt')->shouldReturn('Sel');
        $this->translate('nutrition', $selectColumn, 'en_US', 'pepper')->shouldReturn('[pepper]');
        $this->translate('nutrition', $selectColumn, 'fr_FR', 'pepper')->shouldReturn('[pepper]');
        $this->translate('nutrition', $selectColumn, 'fr_FR', 'unknown')->shouldReturn('[unknown]');
    }
}
