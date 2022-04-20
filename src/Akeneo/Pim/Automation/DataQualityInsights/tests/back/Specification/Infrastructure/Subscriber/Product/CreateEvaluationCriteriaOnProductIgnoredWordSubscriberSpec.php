<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateEvaluationCriteriaOnProductIgnoredWordSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $dataQualityInsightsFeature,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $this->beConstructedWith(
            $dataQualityInsightsFeature,
            $createProductsCriteriaEvaluations,
            $logger,
            $idFactory
        );
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_one_event(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(ProductWordIgnoredEvent::class);
    }

    public function it_schedule_evaluation_when_a_word_is_ignored(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $idFactory
    ) {
        $product = new ProductId(12345);
        $productIdCollection = ProductIdCollection::fromInt(12345);

        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->createAll($productIdCollection)->shouldBeCalled();

        $idFactory->createCollection(['12345'])->willReturn($productIdCollection);

        $this->onIgnoredWord(new ProductWordIgnoredEvent($product));
    }
}
