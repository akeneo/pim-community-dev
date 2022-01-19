<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Normalization;

use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Normalization\TableValuesProcessor;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

class TableValuesProcessorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('entity_name');
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldHaveType(TableValuesProcessor::class);
    }

    function it_processes_a_table_row()
    {
        $tableRow = new TableRow(
            '1234',
            'nutrition',
            'fr_FR',
            'ecommerce',
            Row::fromNormalized([
                ColumnIdGenerator::ingredient() => 'salt',
                ColumnIdGenerator::quantity() => 10,
                ColumnIdGenerator::isAllergenic() => true,
                ColumnIdGenerator::length() => [
                    'amount' => '200',
                    'unit' => 'CENTIMETER',
                ],
            ])
        );

        $this->process($tableRow)->shouldReturn(
            [
                'entity_name' => '1234',
                'attribute' => 'nutrition-fr_FR-ecommerce',
                'ingredient' => 'salt',
                'quantity' => '10',
                'is_allergenic' => '1',
                'length' => '200 CENTIMETER',
            ]
        );
    }

    function it_throws_an_exception_when_processing_anything_but_a_table_row()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('process', [new \stdClass()]);
    }
}
