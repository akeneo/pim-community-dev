<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Reader\Database\TableValuesReader;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Common\FakeCursor;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

class TableValuesReaderSpec extends ObjectBehavior
{
    function let(ProductQueryBuilderFactoryInterface $pqbFactory, StepExecution $stepExecution, JobParameters $jobParameters)
    {
        $jobParameters->get('filters')->willReturn(['table_attribute_code' => 'nutrition']);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $this->beConstructedWith($pqbFactory);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_table_values_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldImplement(TrackableItemReaderInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);

        $this->shouldHaveType(TableValuesReader::class);
    }

    function it_returns_null_during_read_when_no_product_has_the_attribute(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb
    ) {
        $pqbFactory->create([])->willReturn($pqb);
        $pqb->addFilter('nutrition', Operators::IS_NOT_EMPTY, [])->shouldBeCalledOnce()
            ->willReturn($pqb);
        $pqb->execute()->shouldBeCalledOnce()->willReturn(new FakeCursor([]));

        $this->initialize();
        $this->read()->shouldReturn(null);
    }

    function it_reads_table_values(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        StepExecution $stepExecution
    ) {
        $product1 = new Product();
        $product1->setIdentifier('id1');
        $product1->addValue(TableValue::value(
            'nutrition',
            Table::fromNormalized([
                [
                    ColumnIdGenerator::ingredient() => 'salt',
                    ColumnIdGenerator::quantity() => 10,
                ],
            ])
        ));

        $product2 = new Product();
        $product2->setIdentifier('id2');
        $product2->addValue(ScalarValue::value('name', 'the name'));
        $product2->addValue(TableValue::value('packaging', Table::fromNormalized([
            [
                ColumnIdGenerator::parcel() => 'parcel_1',
                ColumnIdGenerator::width() => 100,
            ],
        ])));

        $product3 = new Product();
        $product3->setIdentifier('id3');
        $product3->addValue(TableValue::value(
            'nutrition',
            Table::fromNormalized([
                [
                    ColumnIdGenerator::ingredient() => 'sugar',
                    ColumnIdGenerator::quantity() => 5,
                ],
            ])
        ));

        $pqbFactory->create([])->willReturn($pqb);
        $pqb->addFilter('nutrition', Operators::IS_NOT_EMPTY, [])->shouldBeCalledOnce()
            ->willReturn($pqb);
        $pqb->execute()->shouldBeCalledOnce()->willReturn(new FakeCursor([
            $product1,
            $product2,
            $product3,
        ]));

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalled();

        $this->initialize();
        $this->read()->shouldBeLike(new TableRow('id1', 'nutrition', null, null, Row::fromNormalized([
            ColumnIdGenerator::ingredient() => 'salt',
            ColumnIdGenerator::quantity() => 10,
        ])));
        $this->read()->shouldBeLike(new TableRow('id3', 'nutrition', null, null, Row::fromNormalized([
            ColumnIdGenerator::ingredient() => 'sugar',
            ColumnIdGenerator::quantity() => 5,
        ])));
        $this->read()->shouldReturn(null);
    }

    function it_reads_localizable_and_scopable_table_values(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        StepExecution $stepExecution
    ) {
        $product1 = new Product();
        $product1->setIdentifier('id1');
        $product1->addValue(TableValue::scopableLocalizableValue(
            'nutrition',
            Table::fromNormalized([
                [
                    ColumnIdGenerator::ingredient() => 'salt',
                    ColumnIdGenerator::quantity() => 10,
                ],
                [
                    ColumnIdGenerator::ingredient() => 'pepper',
                    ColumnIdGenerator::quantity() => 4,
                ],
            ]),
            'ecommerce',
            'en_US'
        ));
        $product1->addValue(TableValue::scopableLocalizableValue(
            'nutrition',
            Table::fromNormalized([
                [
                    ColumnIdGenerator::ingredient() => 'pepper',
                    ColumnIdGenerator::quantity() => 10,
                ],
            ]),
            'mobile',
            'fr_FR'
        ));

        $product2 = new Product();
        $product2->setIdentifier('id2');
        $product2->addValue(ScalarValue::value('name', 'the name'));
        $product2->addValue(TableValue::value('packaging', Table::fromNormalized([
            [
                ColumnIdGenerator::parcel() => 'parcel_1',
                ColumnIdGenerator::width() => 100,
            ],
        ])));

        $product3 = new Product();
        $product3->setIdentifier('id3');
        $product3->addValue(TableValue::scopableLocalizableValue(
            'nutrition',
            Table::fromNormalized([
                [
                    ColumnIdGenerator::ingredient() => 'sugar',
                    ColumnIdGenerator::quantity() => 5,
                ],
            ]),
            'mobile',
            'de_DE'
        ));

        $pqbFactory->create([])->willReturn($pqb);
        $pqb->addFilter('nutrition', Operators::IS_NOT_EMPTY, [])->shouldBeCalledOnce()
            ->willReturn($pqb);
        $pqb->execute()->shouldBeCalledOnce()->willReturn(new FakeCursor([
            $product1,
            $product2,
            $product3,
        ]));

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalled();

        $this->initialize();
        $this->read()->shouldBeLike(new TableRow('id1', 'nutrition', 'en_US', 'ecommerce', Row::fromNormalized([
            ColumnIdGenerator::ingredient() => 'salt',
            ColumnIdGenerator::quantity() => 10,
        ])));
        $this->read()->shouldBeLike(new TableRow('id1', 'nutrition', 'en_US', 'ecommerce', Row::fromNormalized([
            ColumnIdGenerator::ingredient() => 'pepper',
            ColumnIdGenerator::quantity() => 4,
        ])));
        $this->read()->shouldBeLike(new TableRow('id1', 'nutrition', 'fr_FR', 'mobile', Row::fromNormalized([
            ColumnIdGenerator::ingredient() => 'pepper',
            ColumnIdGenerator::quantity() => 10,
        ])));
        $this->read()->shouldBeLike(new TableRow('id3', 'nutrition', 'de_DE', 'mobile', Row::fromNormalized([
            ColumnIdGenerator::ingredient() => 'sugar',
            ColumnIdGenerator::quantity() => 5,
        ])));
        $this->read()->shouldReturn(null);
    }

    public function it_counts_the_results(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb
    ) {
        $product = new Product();

        $pqbFactory->create([])->willReturn($pqb);
        $pqb->addFilter('nutrition', Operators::IS_NOT_EMPTY, [])->shouldBeCalledOnce()
            ->willReturn($pqb);
        $pqb->execute()->shouldBeCalledOnce()->willReturn(new FakeCursor(array_fill(0, 42, $product)));

        $this->initialize();
        $this->totalItems()->shouldReturn(42);
    }
}
