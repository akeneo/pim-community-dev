<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet\ComputeCompletenessOfTableAttributeProductsTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class ComputeCompletenessOfTableAttributeProductsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        JobRepositoryInterface $jobRepository,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
        $productQueryBuilderFactory,
        $computeAndPersistProductCompletenesses,
        $jobRepository,
        $productAndAncestorsIndexer
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfTableAttributeProductsTasklet::class);
    }

    /** @test */
    function it_compute_and_persists_the_completeness_of_products_of_table_attributes(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        JobRepositoryInterface $jobRepository,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $productQueryBuilder,
        CursorInterface $cursor,
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('attribute_code')->willReturn('nutrition');
        $jobParameters->get('family_codes')->willReturn(['food']);

        $productQueryBuilderFactory->create()->shouldBeCalled()->willReturn($productQueryBuilder);
        $productQueryBuilder->addFilter('family', Operators::IN_LIST, ['food'])->shouldBeCalled();
        $productQueryBuilder->addFilter('nutrition', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $productQueryBuilder->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $productQueryBuilder->execute()
            ->shouldBeCalledOnce()
            ->willReturn($cursor);

        $cursor->count()->willReturn(2);
        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $cursor->rewind()->shouldBeCalledOnce();
        $cursor->valid()->shouldBeCalledTimes(3)->willReturn(true, true, false);
        $cursor->next()->shouldBeCalledTimes(2);
        $identifierResult1 = new IdentifierResult('identifier1', ProductInterface::class);
        $identifierResult2 = new IdentifierResult('identifier2', ProductInterface::class);
        $cursor->current()->shouldBeCalledTimes(2)->willReturn($identifierResult1, $identifierResult2);

        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['identifier1', 'identifier2'])->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductIdentifiers(['identifier1', 'identifier2'])->shouldBeCalledOnce();

        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledOnce();

        $this->execute();
    }
}
