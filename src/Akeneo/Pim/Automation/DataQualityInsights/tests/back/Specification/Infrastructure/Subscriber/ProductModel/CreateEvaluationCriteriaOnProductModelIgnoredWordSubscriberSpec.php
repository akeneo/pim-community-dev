<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductModelWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetDescendantVariantProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductModelIdsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateEvaluationCriteriaOnProductModelIgnoredWordSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag                                  $dataQualityInsightsFeature,
        CreateCriteriaEvaluations                    $createCriteriaEvaluations,
        LoggerInterface                              $logger,
        GetDescendantVariantProductIdsQueryInterface $getDescendantVariantProductIdsQuery,
        DescendantProductModelIdsQueryInterface      $getDescendantProductModelIdsQuery,
        CreateCriteriaEvaluations                    $createProductsCriteriaEvaluations
    )
    {
        $this->beConstructedWith(
            $dataQualityInsightsFeature,
            $createCriteriaEvaluations,
            $logger,
            $getDescendantVariantProductIdsQuery,
            $getDescendantProductModelIdsQuery,
            $createProductsCriteriaEvaluations
        );
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(ProductModelWordIgnoredEvent::class);
    }

    public function it_creates_criteria_on_ignored_word_suggestion(
        ProductModelInterface $productModel,
                              $dataQualityInsightsFeature,
                              $createCriteriaEvaluations,
                              $getDescendantVariantProductIdsQuery,
                              $getDescendantProductModelIdsQuery,
                              $createProductsCriteriaEvaluations
    )
    {
        $productModel->getId()->willReturn(12345);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createCriteriaEvaluations->createAll(
            ProductIdCollection::fromInt(12345)
        )->shouldBeCalled();

        $productModelId = new ProductId(12345);
        $getDescendantProductModelIdsQuery->fetchFromParentProductModelId($productModelId->toInt())->willReturn([1111, 2222]);
        $createCriteriaEvaluations->createAll(ProductIdCollection::fromInt(1111))->shouldBeCalled();
        $createCriteriaEvaluations->createAll(ProductIdCollection::fromInt(2222))->shouldBeCalled();

        $productModelVariantIds = ['3333', '4444'];
        $getDescendantVariantProductIdsQuery
            ->fromProductModelIds(ProductIdCollection::fromProductId($productModelId))
            ->willReturn($productModelVariantIds);

        $createProductsCriteriaEvaluations->createAll(ProductIdCollection::fromStrings($productModelVariantIds));

        $this->onIgnoredWord(new ProductModelWordIgnoredEvent($productModelId));
    }
}
