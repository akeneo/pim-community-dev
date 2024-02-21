<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Job\IndexFamilyProductsAndProductModelsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\Query\ProductUuidCursorInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Common\FakeCursor;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexFamilyProductsAndProductModelsTaskletSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        ItemReaderInterface $familyReader,
        ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        EntityManagerClearerInterface $cacheClearer,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith(
            $jobRepository,
            $familyReader,
            $productModelQueryBuilderFactory,
            $productAndAncestorsIndexer,
            $productModelDescendantsAndAncestorsIndexer,
            $cacheClearer,
            $messageBus,
            3
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexFamilyProductsAndProductModelsTasklet::class);
    }

    function it_is_an_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_is_an_trackable_tasklet()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
    }

    function it_batches_indexes_products_and_product_models_from_families(
        ItemReaderInterface $familyReader,
        ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        FamilyInterface $familyA,
        FamilyInterface $familyB,
        ProductQueryBuilderInterface $productModelQueryBuilder,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        ProductModelInterface $productModel1,
        MessageBusInterface $messageBus,
        ProductUuidCursorInterface $cursor
    ) {
        $productUuids = [
            Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(),
            Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(),
            Uuid::uuid4(), Uuid::uuid4()
        ];

        $productModelCodes = [
            'minerva',
        ];

        $familyA->getCode()->willReturn('family_code_a');
        $familyB->getCode()->willReturn('family_code_b');
        $familyReader->read()->willReturn($familyA, $familyB, null);

        $productModel1->getCode()->willReturn('minerva');

        $cursor->count()->willReturn(8);
        $cursor->valid()->willReturn(true, true, true, true, true, true, true, true, false);
        $cursor->current()->will(new ReturnPromise($productUuids));
        $cursor->rewind()->shouldBeCalled();
        $cursor->next()->shouldBeCalled();

        $productModelCursor = new FakeCursor([
            $productModel1->getWrappedObject(),
        ]);

        $productModelQueryBuilder
            ->addFilter(Argument::any(), Argument::any(), ['family_code_a', 'family_code_b'])
            ->willReturn();
        $productModelQueryBuilder->execute()->willReturn($productModelCursor);

        $stepExecution->setTotalItems(count($productUuids) + count($productModelCodes))->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $messageBus->dispatch(Argument::type(GetProductUuidsQuery::class))->willReturn(
            new Envelope(new \stdClass(), [new HandledStamp($cursor->getWrappedObject(), '')])
        );

        $productModelQueryBuilderFactory->create()->willReturn($productModelQueryBuilder);

        $productAndAncestorsIndexer->indexFromProductUuids(array_slice($productUuids, 0, 3))->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductUuids(array_slice($productUuids, 3, 3))->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductUuids(array_slice($productUuids, 6, 2))->shouldBeCalledOnce();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($productModelCodes)->shouldBeCalledOnce();

        $stepExecution->incrementProcessedItems(3)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(3)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process', 3)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 3)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 1)->shouldBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(4);

        $this->execute();
    }
}
