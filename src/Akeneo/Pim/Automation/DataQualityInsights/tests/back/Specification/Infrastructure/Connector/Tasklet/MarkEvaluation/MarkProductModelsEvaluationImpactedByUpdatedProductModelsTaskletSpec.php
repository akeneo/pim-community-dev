<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\MarkEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedEntityIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PrepareEvaluationsParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\MarkEvaluation\MarkProductModelsEvaluationImpactedByUpdatedProductModelsTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class MarkProductModelsEvaluationImpactedByUpdatedProductModelsTaskletSpec extends ObjectBehavior
{
    public function let(
        CreateCriteriaEvaluations $createCriteriaEvaluations,
        GetUpdatedEntityIdsQueryInterface $getUpdatedProductModelIdsQuery,
        LoggerInterface $logger,
    ): void {
        $this->beConstructedWith($createCriteriaEvaluations, $getUpdatedProductModelIdsQuery, $logger, 2);
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(MarkProductModelsEvaluationImpactedByUpdatedProductModelsTasklet::class);
    }

    public function it_marks_product_models_evaluation_impacted_by_updated_product_models(
        CreateCriteriaEvaluations $createCriteriaEvaluations,
        GetUpdatedEntityIdsQueryInterface $getUpdatedProductModelIdsQuery,
        LoggerInterface $logger
    ): void {
        $stepExecution = $this->buildStepExecution();
        $this->setStepExecution($stepExecution);

        $productModelIds = [
            ProductModelIdCollection::fromStrings(['42', '657']),
            ProductModelIdCollection::fromStrings(['777'])
        ];

        $getUpdatedProductModelIdsQuery
            ->since(Argument::that(fn (\DateTimeImmutable $updatedSince) => $updatedSince->format('Y-m-d H:i:s') === '2023-02-07 14:23:56'), 2)
            ->willReturn(new \ArrayIterator($productModelIds));

        $createCriteriaEvaluations->createAll($productModelIds[0])->shouldBeCalledOnce();
        $createCriteriaEvaluations->createAll($productModelIds[1])->shouldBeCalledOnce();

        $logger->error(Argument::cetera())->shouldNotBeCalled();

        $this->execute();

        Assert::same($stepExecution->getWriteCount(), 3);
    }

    public function it_does_not_interrupt_the_job_if_an_error_occurs(
        GetUpdatedEntityIdsQueryInterface $getUpdatedProductModelIdsQuery,
        LoggerInterface $logger
    ): void {
        $this->setStepExecution($this->buildStepExecution());

        $getUpdatedProductModelIdsQuery->since(Argument::cetera())->willThrow(new \Exception('error'));

        $logger->error(Argument::cetera())->shouldBeCalledOnce();

        $this->execute();
    }

    private function buildStepExecution(): StepExecution
    {
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters(new JobParameters([
            PrepareEvaluationsParameters::UPDATED_SINCE_PARAMETER => '2023-02-07 14:23:56',
        ]));

        return new StepExecution('mark_product_models_evaluation_impacted_by_updated_product_models', $jobExecution);
    }
}
