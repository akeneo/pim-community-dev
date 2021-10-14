<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Job\IndexFamilyProductsAndProductModelsTasklet;
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
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexFamilyProductsAndProductModelsTaskletSpec extends ObjectBehavior
{
    private const PRODUCT_BATCHES = [
        ['batchA_product1', 'batchA_product2', 'batchA_product3'],
        ['batchB_product1', 'batchB_product2', 'batchB_product3'],
        ['batchC_product1', 'batchC_product2'],
    ];
    private const PRODUCT_IDENTIFIERS = [
        'batchA_product1',
        'batchA_product2',
        'batchA_product3',
        'batchB_product1',
        'batchB_product2',
        'batchB_product3',
        'batchC_product1',
        'batchC_product2'
    ];
    private const PRODUCT_MODEL_CODES = [
        'minerva',
    ];

    function let(
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        ItemReaderInterface $familyReader,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->beConstructedWith(
            $jobRepository,
            $familyReader,
            $productQueryBuilderFactory,
            $productModelQueryBuilderFactory,
            $productAndAncestorsIndexer,
            $productModelDescendantsAndAncestorsIndexer,
            $cacheClearer,
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
        ProductInterface $productA1,
        ProductInterface $productA2,
        ProductInterface $productA3,
        ProductInterface $productB1,
        ProductInterface $productB2,
        ProductInterface $productB3,
        ProductInterface $productC1,
        ProductInterface $productC2,
        ProductModelInterface $productModel1
    ) {
        $familyA->getCode()->willReturn('family_code_a');
        $familyB->getCode()->willReturn('family_code_b');
        $familyReader->read()->willReturn($familyA, $familyB, null);

        $productA1->getIdentifier()->willReturn('batchA_product1');
        $productA2->getIdentifier()->willReturn('batchA_product2');
        $productA3->getIdentifier()->willReturn('batchA_product3');
        $productB1->getIdentifier()->willReturn('batchB_product1');
        $productB2->getIdentifier()->willReturn('batchB_product2');
        $productB3->getIdentifier()->willReturn('batchB_product3');
        $productC1->getIdentifier()->willReturn('batchC_product1');
        $productC2->getIdentifier()->willReturn('batchC_product2');
        $productModel1->getCode()->willReturn('minerva');

        $productCursor = new FakeCursor([
            $productA1->getWrappedObject(),
            $productA2->getWrappedObject(),
            $productA3->getWrappedObject(),
            $productB1->getWrappedObject(),
            $productB2->getWrappedObject(),
            $productB3->getWrappedObject(),
            $productC1->getWrappedObject(),
            $productC2->getWrappedObject(),
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

        $stepExecution->setTotalItems(count(self::PRODUCT_IDENTIFIERS) + count(self::PRODUCT_MODEL_CODES))->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);
        $productModelQueryBuilderFactory->create()->willReturn($productModelQueryBuilder);

        $productAndAncestorsIndexer->indexFromProductIdentifiers(self::PRODUCT_BATCHES[0])->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductIdentifiers(self::PRODUCT_BATCHES[1])->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductIdentifiers(self::PRODUCT_BATCHES[2])->shouldBeCalledOnce();
        $productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes(self::PRODUCT_MODEL_CODES)->shouldBeCalledOnce();

        $stepExecution->incrementProcessedItems(count(self::PRODUCT_BATCHES[0]))->shouldBeCalled();
        $stepExecution->incrementProcessedItems(count(self::PRODUCT_BATCHES[1]))->shouldBeCalled();
        $stepExecution->incrementProcessedItems(count(self::PRODUCT_BATCHES[2]))->shouldBeCalled();
        $stepExecution->incrementProcessedItems(count(self::PRODUCT_MODEL_CODES))->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process', count(self::PRODUCT_BATCHES[0]))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', count(self::PRODUCT_BATCHES[1]))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', count(self::PRODUCT_BATCHES[2]))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', count(self::PRODUCT_MODEL_CODES))->shouldBeCalled();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(4);

        $this->execute();
    }
}
