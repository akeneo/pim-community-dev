<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class EvaluateProductsAndProductModelsCriteriaTaskletSpec extends ObjectBehavior
{
    public function let(
        GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery,
        GetProductIdsToEvaluateQueryInterface $getProductModelsIdsToEvaluateQuery,
        EvaluateProducts                      $evaluateProducts,
        EvaluateProductModels                 $evaluateProductModels,
    ): void
    {
        $this->beConstructedWith($getProductIdsToEvaluateQuery, $getProductModelsIdsToEvaluateQuery, $evaluateProducts, $evaluateProductModels, 1000, 2, 0, 0);
    }

    public function it_evaluates_products_and_product_models(
        $getProductIdsToEvaluateQuery,
        $getProductModelsIdsToEvaluateQuery,
        $evaluateProducts,
        $evaluateProductModels,
    ): void
    {
        $stepExecution = new StepExecution('name', new JobExecution());
        $this->setStepExecution($stepExecution);

        $productIds = [ProductIdCollection::fromInts([1, 2]), ProductIdCollection::fromInts([3])];
        $getProductIdsToEvaluateQuery->execute(1000, 2)->willReturn(new \ArrayIterator($productIds));
        $evaluateProducts->__invoke($productIds[0])->shouldBeCalled();
        $evaluateProducts->__invoke($productIds[1])->shouldBeCalled();

        $productModelIds = [ProductIdCollection::fromInts([4, 5]), ProductIdCollection::fromInts([6, 7])];
        $getProductModelsIdsToEvaluateQuery->execute(1000, 2)->willReturn(new \ArrayIterator($productModelIds));
        $evaluateProductModels->__invoke($productModelIds[0])->shouldBeCalled();
        $evaluateProductModels->__invoke($productModelIds[1])->shouldBeCalled();

        $this->execute();

        $evaluationSummary = $stepExecution->getSummaryInfo('evaluations');
        Assert::same($evaluationSummary['products']['count'], 3);
        Assert::same($evaluationSummary['product_models']['count'], 4);
    }
}
