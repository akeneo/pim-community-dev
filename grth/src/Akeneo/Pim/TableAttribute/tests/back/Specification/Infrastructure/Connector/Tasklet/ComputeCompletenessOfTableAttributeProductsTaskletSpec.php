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

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet\ComputeCompletenessOfTableAttributeProductsTasklet;
use Akeneo\Test\Common\FakeCursor;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class ComputeCompletenessOfTableAttributeProductsTaskletSpec extends ObjectBehavior
{
    function let(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        JobRepositoryInterface $jobRepository,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        StepExecution $stepExecution,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith(
            $computeAndPersistProductCompletenesses,
            $jobRepository,
            $productAndAncestorsIndexer,
            $messageBus
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfTableAttributeProductsTasklet::class);
    }

    /** @test */
    function it_compute_and_persists_the_completeness_of_products_of_table_attributes(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        JobRepositoryInterface $jobRepository,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        MessageBusInterface $messageBus
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4()];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('attribute_code')->willReturn('nutrition');
        $jobParameters->get('family_codes')->willReturn(['food']);

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();

        $cursor = new FakeCursor($uuids);
        $messageBus->dispatch(Argument::type(GetProductUuidsQuery::class))->willReturn(
            new Envelope(new \stdClass(), [new HandledStamp($cursor, '')])
        );

        $computeAndPersistProductCompletenesses->fromProductUuids($uuids)->shouldBeCalledOnce();
        $productAndAncestorsIndexer->indexFromProductUuids($uuids)->shouldBeCalledOnce();

        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledOnce();

        $this->execute();
    }
}
