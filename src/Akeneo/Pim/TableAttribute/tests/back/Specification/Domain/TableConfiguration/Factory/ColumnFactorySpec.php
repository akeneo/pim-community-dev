<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\tests\back\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class ColumnFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'text' => TextColumn::class,
            'number' => NumberColumn::class,
            'boolean' => BooleanColumn::class,
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ColumnFactory::class);
    }

    function it_returns_a_text_column()
    {
        $column = $this->createFromNormalized([
            'id' => ColumnIdGenerator::ingredient(),
            'data_type' => 'text',
            'code' => 'ingredient',
            'labels' => [],
        ]);
        $column->shouldHaveType(TextColumn::class);
        $column->id()->shouldBeLike(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $column->code()->shouldBeLike(ColumnCode::fromString('ingredient'));
    }

    function it_returns_a_number_column()
    {
        $column = $this->createFromNormalized([
            'id' => ColumnIdGenerator::quantity(),
            'data_type' => 'number',
            'code' => 'quantities',
            'labels' => [],
        ]);
        $column->shouldHaveType(NumberColumn::class);
        $column->id()->shouldBeLike(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $column->code()->shouldBeLike(ColumnCode::fromString('quantities'));
    }

    function it_returns_a_boolean_column()
    {
        $column = $this->createFromNormalized([
            'id' => ColumnIdGenerator::quantity(),
            'data_type' => 'boolean',
            'code' => 'quantities',
            'labels' => [],
        ]);
        $column->shouldHaveType(BooleanColumn::class);
        $column->id()->shouldBeLike(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $column->code()->shouldBeLike(ColumnCode::fromString('quantities'));
    }

    function it_throws_an_exception_when_data_type_is_not_provided()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createFromNormalized', [
            [
                'code' => 'quantities',
                'labels' => [],
            ],
        ]);
    }

    function it_throws_an_exception_when_data_type_is_not_a_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createFromNormalized', [
            [
                'data_type' => ['text'],
                'code' => 'quantities',
                'labels' => [],
            ],
        ]);
    }

    function it_throws_an_exception_when_data_type_is_unknown()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createFromNormalized', [
            [
                'data_type' => 'unknown',
                'code' => 'quantities',
                'labels' => [],
            ],
        ]);
    }
}
