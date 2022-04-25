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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductModelWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetDescendantVariantProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
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
        CreateCriteriaEvaluations                    $createProductsCriteriaEvaluations,
        ProductEntityIdFactoryInterface              $productModelIdFactory,
        ProductEntityIdFactoryInterface              $productIdFactory
    )
    {
        $this->beConstructedWith(
            $dataQualityInsightsFeature,
            $createCriteriaEvaluations,
            $logger,
            $getDescendantVariantProductIdsQuery,
            $getDescendantProductModelIdsQuery,
            $createProductsCriteriaEvaluations,
            $productModelIdFactory,
            $productIdFactory
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
        $dataQualityInsightsFeature,
        $createCriteriaEvaluations,
        $getDescendantVariantProductIdsQuery,
        $getDescendantProductModelIdsQuery,
        $createProductsCriteriaEvaluations,
        $productModelIdFactory,
        $productIdFactory
    )
    {
        $productModelId = ProductModelId::fromString('12345');
        $productModelIdCollection = ProductModelIdCollection::fromStrings(['12345']);
        $productModelIdFactory->createCollection(['12345'])->willReturn($productModelIdCollection);

        $subProductModelIdA = ProductModelId::fromString('1111');
        $subProductModelIdB = ProductModelId::fromString('2222');
        $productModelIdFactory->create('1111')->willReturn($subProductModelIdA);
        $productModelIdFactory->create('2222')->willReturn($subProductModelIdB);

        $subProductModelIdCollectionA = ProductModelIdCollection::fromStrings(['1111']);
        $subProductModelIdCollectionB = ProductModelIdCollection::fromStrings(['2222']);
        $productModelIdFactory->createCollection(['1111'])->willReturn($subProductModelIdCollectionA);
        $productModelIdFactory->createCollection(['2222'])->willReturn($subProductModelIdCollectionB);

        $productVariantIdCollection = ProductIdCollection::fromStrings(['3333', '4444']);
        $productIdFactory->createCollection(['3333', '4444'])->willReturn($productVariantIdCollection);

        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createCriteriaEvaluations->createAll($productModelIdCollection)->shouldBeCalled();

        $getDescendantProductModelIdsQuery->fetchFromParentProductModelId($productModelId->toInt())->willReturn([1111, 2222]);
        $createCriteriaEvaluations->createAll($subProductModelIdCollectionA)->shouldBeCalled();
        $createCriteriaEvaluations->createAll($subProductModelIdCollectionB)->shouldBeCalled();

        $productModelVariantIds = ['3333', '4444'];
        $getDescendantVariantProductIdsQuery
            ->fromProductModelIds($productModelIdCollection)
            ->willReturn($productModelVariantIds);

        $createProductsCriteriaEvaluations->createAll($productVariantIdCollection);

        $this->onIgnoredWord(new ProductModelWordIgnoredEvent($productModelId));
    }
}
