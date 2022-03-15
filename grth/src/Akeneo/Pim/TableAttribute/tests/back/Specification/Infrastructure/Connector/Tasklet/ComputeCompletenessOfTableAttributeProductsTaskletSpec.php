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

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
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
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        JobRepositoryInterface $jobRepository,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productQueryBuilderFactory,
            $completenessCalculator,
            $saveProductCompletenesses,
            $jobRepository
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfTableAttributeProductsTasklet::class);
    }

    /** @test */
    function it_compute_and_persists_the_completeness_of_products_of_table_attributes(
        StepExecution $stepExecution,
        SaveProductCompletenesses $saveProductCompletenesses,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderInterface $productQueryBuilder,
        CompletenessCalculator $completenessCalculator,
        JobRepositoryInterface $jobRepository,
        CursorInterface $cursor,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('attribute_code')->willReturn('nutrition');
        $jobParameters->get('family_codes')->willReturn(['food']);

        $productQueryBuilderFactory->create()->shouldBeCalled()->willReturn($productQueryBuilder);
        $productQueryBuilder->addFilter('family', Operators::IN_LIST, ['food'])->shouldBeCalled();
        $productQueryBuilder->addFilter('nutrition', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $productQueryBuilder->execute()
            ->shouldBeCalledOnce()
            ->willReturn($cursor);

        $cursor->count()->willReturn(2);
        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $cursor->rewind()->shouldBeCalledOnce();
        $cursor->valid()->shouldBeCalledTimes(3)->willReturn(true, true, false);
        $cursor->next()->shouldBeCalledTimes(2);
        $cursor->current()->shouldBeCalledTimes(2)->willReturn('identifier1', 'identifier2');

        $completenessCollection = [
            'identifier1' => new ProductCompletenessWithMissingAttributeCodesCollection(5, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
            ]),
            'identifier2' => new ProductCompletenessWithMissingAttributeCodesCollection(5, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
            ]),
        ];
        $completenessCalculator->fromProductIdentifiers(['identifier1', 'identifier2'])
            ->shouldBeCalledOnce()
            ->willReturn($completenessCollection);

        $saveProductCompletenesses->saveAll($completenessCollection)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledOnce();

        $this->execute();
    }
}
