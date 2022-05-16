<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

final class GetScoresByCriteriaStrategySpec extends ObjectBehavior
{
    public function let(FeatureFlag $allCriteriaFeature)
    {
        $this->beConstructedWith($allCriteriaFeature);
    }

    public function it_gets_scores_all_criteria_when_the_feature_dqi_all_criteria_is_enabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(true);

        $scores = $this->givenScores();
        $this->__invoke($scores)->shouldReturn($scores->allCriteria());
    }

    public function it_gets_scores_partial_criteria_when_the_feature_dqi_all_criteria_is_disabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(false);

        $scores = $this->givenScores();
        $this->__invoke($scores)->shouldReturn($scores->partialCriteria());
    }

    private function givenScores(): Read\Scores
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        return new Read\Scores(
            (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(76)),
            (new ChannelLocaleRateCollection)->addRate($channel, $locale, new Rate(65))
        );
    }
}
