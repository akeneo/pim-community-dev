<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class EvaluateProductsAndProductModelsCriteriaTaskletSpec extends ObjectBehavior
{
    public function let(
        GetEntityIdsToEvaluateQueryInterface $getProductUuidsToEvaluateQuery,
        GetEntityIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery,
        EvaluateProducts                     $evaluateProducts,
        EvaluateProductModels                $evaluateProductModels,
    ): void
    {
        $this->beConstructedWith($getProductUuidsToEvaluateQuery, $getProductModelsIdsToEvaluateQuery, $evaluateProducts, $evaluateProductModels, 1000, 2, 0, 0);
    }

    public function it_evaluates_products_and_product_models(
        GetEntityIdsToEvaluateQueryInterface $getProductUuidsToEvaluateQuery,
        GetEntityIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery,
        EvaluateProducts                     $evaluateProducts,
        EvaluateProductModels                $evaluateProductModels
    ): void
    {
        $stepExecution = new StepExecution('name', new JobExecution());
        $this->setStepExecution($stepExecution);


        $productUuids = [ProductUuidCollection::fromStrings([
            '6d125b99-d971-41d9-a264-b020cd486aee',
            'fef37e64-a963-47a9-b087-2cc67968f0a2'
        ]), ProductUuidCollection::fromStrings([
            'df470d52-7723-4890-85a0-e79be625e2ed'
        ])];
        $getProductUuidsToEvaluateQuery->execute(1000, 2)->willReturn(new \ArrayIterator($productUuids));
        $evaluateProducts->forPendingCriteria($productUuids[0])->shouldBeCalled();
        $evaluateProducts->forPendingCriteria($productUuids[1])->shouldBeCalled();

        $productModelIds = [ProductModelIdCollection::fromStrings(['4', '5']), ProductModelIdCollection::fromStrings(['6', '7'])];
        $getProductModelsIdsToEvaluateQuery->execute(1000, 2)->willReturn(new \ArrayIterator($productModelIds));
        $evaluateProductModels->forPendingCriteria($productModelIds[0])->shouldBeCalled();
        $evaluateProductModels->forPendingCriteria($productModelIds[1])->shouldBeCalled();

        $this->execute();

        $evaluationSummary = $stepExecution->getSummaryInfo('evaluations');
        Assert::same($evaluationSummary['products']['count'], 3);
        Assert::same($evaluationSummary['product_models']['count'], 4);
    }
}
