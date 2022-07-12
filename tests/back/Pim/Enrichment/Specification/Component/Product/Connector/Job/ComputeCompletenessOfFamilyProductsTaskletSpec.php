<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlFindProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeCompletenessOfFamilyProductsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ComputeCompletenessOfFamilyProductsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        Connection $connection
    ) {
        $this->beConstructedWith(
            $productQueryBuilderFactory,
            $familyReader,
            $cacheClearer,
            $jobRepository,
            $completenessCalculator,
            $saveProductCompletenesses,
            new SqlFindProductUuids($connection->getWrappedObject())
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfFamilyProductsTasklet::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_does_nothing_if_there_is_no_family(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $familyReader->read()->shouldBeCalledOnce()->willReturn(null);
        $productQueryBuilderFactory->create()->shouldNotBeCalled();
        $completenessCalculator->fromProductUuids(Argument::any())->shouldNotBeCalled();
        $saveProductCompletenesses->saveAll(Argument::any())->shouldNotBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_products_of_family(
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes,
        FamilyInterface $familyTshirt,
        Connection $connection
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $familyReader->read()->shouldBeCalledTimes(3)->willReturn($familyShoes, $familyTshirt, null);
        $familyShoes->getCode()->willReturn('Shoes');
        $familyTshirt->getCode()->willReturn('Tshirt');

        $productQueryBuilder->addFilter('family', 'IN', ['Shoes', 'Tshirt'])->shouldBeCalled();
        $productQueryBuilder->execute()->shouldBeCalled()->willReturn(
            new IdentifierResultCursor([
                new IdentifierResult('product_shoes_1', ProductInterface::class),
                new IdentifierResult('product_shoes_2', ProductInterface::class),
                new IdentifierResult('product_tshirt_1', ProductInterface::class),
                new IdentifierResult('product_tshirt_2', ProductInterface::class),
            ]),
        );

        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['product_shoes_1', 'product_shoes_2', 'product_tshirt_1', 'product_tshirt_2']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'product_shoes_1' => $uuids[0],
                'product_shoes_2' => $uuids[1],
                'product_tshirt_1' => $uuids[2],
                'product_tshirt_2' => $uuids[3],
            ]);

        $completenessCalculator->fromProductUuids($uuids)->shouldBeCalled()->willReturn(['completeness_collection']);
        $saveProductCompletenesses->saveAll(['completeness_collection'])->shouldBeCalled();

        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('process', 4)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(4)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_more_than_1000_products(
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $familyReader->read()->shouldBeCalledTimes(2)->willReturn($familyShoes, null);
        $familyShoes->getCode()->willReturn('Shoes');

        $productQueryBuilder->addFilter('family', 'IN', ['Shoes'])->shouldBeCalled();
        $productQueryBuilder->execute()->shouldBeCalled()->willReturn(
            new IdentifierResultCursor(array_map(function (int $i): IdentifierResult {
                return new IdentifierResult('product_' . $i, ProductInterface::class);
            }, range(1, 1006)))
        );

        $completenessCalculator->fromProductUuids(Argument::type('array'))->shouldBeCalledTimes(11);
        $saveProductCompletenesses->saveAll(Argument::type('array'))->shouldBeCalledTimes(11);
        $cacheClearer->clear()->shouldBeCalledTimes(10);

        $stepExecution->setTotalItems(1006)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('process', 100)->shouldBeCalledTimes(10);
        $stepExecution->incrementSummaryInfo('process', 6)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(100)->shouldBeCalledTimes(10);
        $stepExecution->incrementProcessedItems(6)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(11);

        $this->execute();
    }
}

class IdentifierResultCursor extends \ArrayIterator implements CursorInterface
{
}
