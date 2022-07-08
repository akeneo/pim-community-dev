<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

final class ProductKeyIndicatorsByFeatureRegistrySpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $allCriteriaFeature,
        ComputeProductsKeyIndicator $keyIndicatorA,
        ComputeProductsKeyIndicator $keyIndicatorB,
        ComputeProductsKeyIndicator $allCriteriaOnlyKeyIndicatorB,
    ) {
        $this->beConstructedWith($allCriteriaFeature);

        $keyIndicatorA->getCode()->willReturn(new KeyIndicatorCode('ki_A'));
        $keyIndicatorB->getCode()->willReturn(new KeyIndicatorCode('ki_B'));
        $allCriteriaOnlyKeyIndicatorB->getCode()->willReturn(new KeyIndicatorCode('all_criteria_only_ki'));
        
        $this->register($keyIndicatorA, null);
        $this->register($keyIndicatorB, 'whatever_feature');
        $this->register($allCriteriaOnlyKeyIndicatorB, 'data_quality_insights_all_criteria');
    }

    public function it_gets_all_key_indicators_when_all_criteria_feature_is_enabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(true);

        $this->getCodes()->shouldBeLike([
            new KeyIndicatorCode('ki_A'),
            new KeyIndicatorCode('ki_B'),
            new KeyIndicatorCode('all_criteria_only_ki'),
        ]);
    }

    public function it_gets_partial_key_indicators_when_all_criteria_feature_is_disabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(false);

        $this->getCodes()->shouldBeLike([
            new KeyIndicatorCode('ki_A'),
            new KeyIndicatorCode('ki_B'),
        ]);
    }
}
