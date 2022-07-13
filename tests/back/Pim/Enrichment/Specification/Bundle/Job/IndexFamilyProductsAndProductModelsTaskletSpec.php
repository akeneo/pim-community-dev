<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Job\IndexFamilyProductsAndProductModelsTasklet;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlFindProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Common\FakeCursor;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexFamilyProductsAndProductModelsTaskletSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        ItemReaderInterface $familyReader,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        EntityManagerClearerInterface $cacheClearer,
        Connection $connection
    ) {
        $this->beConstructedWith(
            $jobRepository,
            $familyReader,
            $productQueryBuilderFactory,
            $productModelQueryBuilderFactory,
            $productAndAncestorsIndexer,
            $productModelDescendantsAndAncestorsIndexer,
            $cacheClearer,
            new SqlFindProductUuids($connection->getWrappedObject()),
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
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        FamilyInterface $familyA,
        FamilyInterface $familyB,
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductQueryBuilderInterface $productModelQueryBuilder,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        ProductModelInterface $productModel1,
        Connection $connection
    ) {
        $productIndentifiers = [
            'batchA_product1' => Uuid::uuid4(),
            'batchA_product2' => Uuid::uuid4(),
            'batchA_product3' => Uuid::uuid4(),
            'batchB_product1' => Uuid::uuid4(),
            'batchB_product2' => Uuid::uuid4(),
            'batchB_product3' => Uuid::uuid4(),
            'batchC_product1' => Uuid::uuid4(),
            'batchC_product2' => Uuid::uuid4()
        ];

        $productModelCodes = [
            'minerva',
        ];

        $familyA->getCode()->willReturn('family_code_a');
        $familyB->getCode()->willReturn('family_code_b');
        $familyReader->read()->willReturn($familyA, $familyB, null);

        $productModel1->getCode()->willReturn('minerva');

        $productCursor = new FakeCursor([
            new IdentifierResult(\array_keys($productIndentifiers)[0], ProductInterface::class),
            new IdentifierResult(\array_keys($productIndentifiers)[1], ProductInterface::class),
            new IdentifierResult(\array_keys($productIndentifiers)[2], ProductInterface::class),
            new IdentifierResult(\array_keys($productIndentifiers)[3], ProductInterface::class),
            new IdentifierResult(\array_keys($productIndentifiers)[4], ProductInterface::class),
            new IdentifierResult(\array_keys($productIndentifiers)[5], ProductInterface::class),
            new IdentifierResult(\array_keys($productIndentifiers)[6], ProductInterface::class),
            new IdentifierResult(\array_keys($productIndentifiers)[7], ProductInterface::class),
        ]);

        $productModelCursor = new FakeCursor([
            $productModel1->getWrappedObject(),
        ]);

        $productQueryBuilder
            ->addFilter(Argument::any(), Argument::any(), ['family_code_a', 'family_code_b'])
            ->willReturn();

        $productModelQueryBuilder
            ->addFilter(Argument::any(), Argument::any(), ['family_code_a', 'family_code_b'])
            ->willReturn();

        $productQueryBuilder->execute()->willReturn($productCursor);
        $productModelQueryBuilder->execute()->willReturn($productModelCursor);

        $stepExecution->setTotalItems(count($productIndentifiers) + count($productModelCodes))->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);
        $productModelQueryBuilderFactory->create()->willReturn($productModelQueryBuilder);

        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['batchA_product1', 'batchA_product2', 'batchA_product3']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'batchA_product1' => $productIndentifiers['batchA_product1'],
                'batchA_product2' => $productIndentifiers['batchA_product2'],
                'batchA_product3' => $productIndentifiers['batchA_product3'],
            ]);
        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['batchB_product1', 'batchB_product2', 'batchB_product3']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'batchB_product1' => $productIndentifiers['batchB_product1'],
                'batchB_product2' => $productIndentifiers['batchB_product2'],
                'batchB_product3' => $productIndentifiers['batchB_product3'],
            ]);
        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['batchC_product1', 'batchC_product2', 'batchC_product3']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'batchC_product1' => $productIndentifiers['batchC_product1'],
                'batchC_product2' => $productIndentifiers['batchC_product2'],
                'batchC_product3' => $productIndentifiers['batchC_product3'],
            ]);

        $productAndAncestorsIndexer->indexFromProductUuids(array_slice(\array_values($productIndentifiers), 0, 3))->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductUuids(array_slice(\array_values($productIndentifiers), 3, 3))->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductUuids(array_slice(\array_values($productIndentifiers), 6, 2))->shouldBeCalledOnce();
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
