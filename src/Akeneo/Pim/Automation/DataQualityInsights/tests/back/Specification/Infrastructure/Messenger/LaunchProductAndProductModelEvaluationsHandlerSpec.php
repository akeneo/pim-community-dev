<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductUuidsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsHandler;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandlerSpec extends ObjectBehavior
{
    public function let(
        EvaluateProducts $evaluateProducts,
        EvaluateProductModels $evaluateProductModels,
        GetOutdatedProductUuidsByDateAndCriteriaQueryInterface $getOutdatedProductUuids,
        GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface $getOutdatedProductModelIds,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith(
            $evaluateProducts,
            $evaluateProductModels,
            $getOutdatedProductUuids,
            $getOutdatedProductModelIds,
            $logger
        );
    }

    public function it_is_a_launch_products_and_product_models_evaluation_handler(): void
    {
        $this->shouldHaveType(LaunchProductAndProductModelEvaluationsHandler::class);
    }

    public function it_launches_products_and_product_models_evaluations_for_all_criteria_only_for_outdated_products_and_product_models(
        EvaluateProducts $evaluateProducts,
        EvaluateProductModels $evaluateProductModels,
        GetOutdatedProductUuidsByDateAndCriteriaQueryInterface $getOutdatedProductUuids,
        GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface $getOutdatedProductModelIds,
    ): void {
        $completenessCriterionCode = new CriterionCode('enrichment_completeness');
        $spellcheckCriterionCode = new CriterionCode('consistency_spellcheck');

        $productModelCriteria = [$completenessCriterionCode, $spellcheckCriterionCode];

        $productUuid1 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid2 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid3 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2, $productUuid3]);
        $outDatedProductUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2]);

        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(123);
        $productModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1, $productModelId2]);
        $outDatedProductModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1]);

        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-03-16 14:46:32'),
            $productUuids,
            $productModelIds,
            []
        );

        $getOutdatedProductUuids->__invoke($productUuids, $message->datetime, [])->willReturn($outDatedProductUuids);
        $getOutdatedProductModelIds->__invoke($productModelIds, $message->datetime, [])->willReturn($outDatedProductModelIds);

        $evaluateProducts->forCriteria($outDatedProductUuids, [])->shouldBeCalledOnce();
        $evaluateProductModels->forCriteria($outDatedProductModelIds, [])->shouldBeCalledOnce();

        $this->__invoke($message);
    }

    public function it_launches_products_and_product_models_evaluations_for_only_given_criteria(
        EvaluateProducts $evaluateProducts,
        EvaluateProductModels $evaluateProductModels,
        GetOutdatedProductUuidsByDateAndCriteriaQueryInterface $getOutdatedProductUuids,
        GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface $getOutdatedProductModelIds,
    ): void {
        $criteriaToEvaluate = [
            new CriterionCode('enrichment_completeness'),
            new CriterionCode('enrichment_image'),
        ];

        $productUuid1 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid2 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2]);

        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(123);
        $productModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1, $productModelId2]);

        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-03-16 14:46:32'),
            $productUuids,
            $productModelIds,
            ['enrichment_completeness', 'enrichment_image']
        );

        $getOutdatedProductUuids->__invoke($productUuids, $message->datetime, $message->criteriaToEvaluate)->willReturn($productUuids);
        $getOutdatedProductModelIds->__invoke($productModelIds, $message->datetime, $message->criteriaToEvaluate)->willReturn($productModelIds);

        $evaluateProducts->forCriteria($productUuids, $criteriaToEvaluate)->shouldBeCalledOnce();
        $evaluateProductModels->forCriteria($productModelIds, $criteriaToEvaluate)->shouldBeCalledOnce();

        $this->__invoke($message);
    }

    public function it_does_not_launch_evaluations_if_there_are_no_outdated_products_and_product_models(
        EvaluateProducts $evaluateProducts,
        EvaluateProductModels $evaluateProductModels,
        GetOutdatedProductUuidsByDateAndCriteriaQueryInterface $getOutdatedProductUuids,
        GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface $getOutdatedProductModelIds,
    ): void {
        $productUuid1 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid2 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuids = ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2]);

        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(123);
        $productModelIds = ProductModelIdCollection::fromProductModelIds([$productModelId1, $productModelId2]);

        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-03-16 14:46:32'),
            $productUuids,
            $productModelIds,
            ['enrichment_completeness', 'enrichment_image']
        );

        $getOutdatedProductUuids->__invoke($productUuids, $message->datetime, $message->criteriaToEvaluate)
            ->willReturn(ProductUuidCollection::fromProductUuids([]));
        $getOutdatedProductModelIds->__invoke($productModelIds, $message->datetime, $message->criteriaToEvaluate)
            ->willReturn(ProductModelIdCollection::fromProductModelIds([]));

        $evaluateProducts->forCriteria(Argument::any())->shouldNotBeCalled();
        $evaluateProductModels->forCriteria(Argument::any())->shouldNotBeCalled();

        $this->__invoke($message);
    }
}
