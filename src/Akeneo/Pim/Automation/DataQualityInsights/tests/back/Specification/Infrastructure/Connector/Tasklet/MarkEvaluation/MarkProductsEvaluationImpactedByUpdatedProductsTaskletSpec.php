<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\MarkEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedEntityIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PrepareEvaluationsParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\MarkEvaluation\MarkProductsEvaluationImpactedByUpdatedProductsTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class MarkProductsEvaluationImpactedByUpdatedProductsTaskletSpec extends ObjectBehavior
{
    public function let(
        CreateCriteriaEvaluations $createCriteriaEvaluations,
        GetUpdatedEntityIdsQueryInterface $getUpdatedProductIdsQuery,
        LoggerInterface $logger,
    ): void {
        $this->beConstructedWith($createCriteriaEvaluations, $getUpdatedProductIdsQuery, $logger, 2);
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(MarkProductsEvaluationImpactedByUpdatedProductsTasklet::class);
    }

    public function it_marks_products_evaluation_impacted_by_updated_products(
        CreateCriteriaEvaluations $createCriteriaEvaluations,
        GetUpdatedEntityIdsQueryInterface $getUpdatedProductIdsQuery,
        LoggerInterface $logger
    ): void {
        $stepExecution = $this->buildStepExecution();
        $this->setStepExecution($stepExecution);

        $productUuids = [
            ProductUuidCollection::fromStrings(['6d125b99-d971-41d9-a264-b020cd486aee', 'fef37e64-a963-47a9-b087-2cc67968f0a2']),
            ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed'])
        ];

        $getUpdatedProductIdsQuery
            ->since(Argument::that(fn (\DateTimeImmutable $updatedSince) => $updatedSince->format('Y-m-d H:i:s') === '2023-02-07 14:23:56'), 2)
            ->willReturn(new \ArrayIterator($productUuids));

        $createCriteriaEvaluations->createAll($productUuids[0])->shouldBeCalled();
        $createCriteriaEvaluations->createAll($productUuids[1])->shouldBeCalled();

        $logger->error(Argument::cetera())->shouldNotBeCalled();

        $this->execute();

        Assert::same($stepExecution->getWriteCount(), 3);
    }

    public function it_does_not_interrupt_the_job_if_an_error_occurs(
        GetUpdatedEntityIdsQueryInterface $getUpdatedProductIdsQuery,
        LoggerInterface $logger
    ): void {
        $this->setStepExecution($this->buildStepExecution());

        $getUpdatedProductIdsQuery->since(Argument::cetera())->willThrow(new \Exception('error'));

        $logger->error(Argument::cetera())->shouldBeCalled();

        $this->execute();
    }

    private function buildStepExecution(): StepExecution
    {
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters(new JobParameters([
            PrepareEvaluationsParameters::UPDATED_SINCE_PARAMETER => '2023-02-07 14:23:56',
        ]));

        return new StepExecution('mark_products_evaluation_impacted_by_updated_products', $jobExecution);
    }
}
