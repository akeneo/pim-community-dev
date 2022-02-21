<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
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

        $productIds = [[1, 2], [3]];
        $getProductIdsToEvaluateQuery->execute(1000, 2)->willReturn(new \ArrayIterator($productIds));
        $evaluateProducts->__invoke([1, 2])->shouldBeCalled();
        $evaluateProducts->__invoke([3])->shouldBeCalled();

        $productModelIds = [[4, 5], [6, 7]];
        $getProductModelsIdsToEvaluateQuery->execute(1000, 2)->willReturn(new \ArrayIterator($productModelIds));
        $evaluateProductModels->__invoke([4, 5])->shouldBeCalled();
        $evaluateProductModels->__invoke([6, 7])->shouldBeCalled();

        $this->execute();
        $result = $stepExecution->getTrackingData();
        Assert::same($result['evaluations']['products']['count'], 3);
        Assert::same($result['evaluations']['product_models']['count'], 4);
    }
}

