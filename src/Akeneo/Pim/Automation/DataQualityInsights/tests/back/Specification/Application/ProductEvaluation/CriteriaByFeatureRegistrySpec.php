<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag\AllCriteriaFeature;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

class CriteriaByFeatureRegistrySpec extends ObjectBehavior
{
    private ?CriterionCode $criterionCodeWithoutFeature;
    private ?CriterionCode $criterionCodeWhateverFeature;
    private ?CriterionCode $criterionCodeAllFeatureOnly;

    public function let(
        FeatureFlag $allCriteriaFeature,
        EvaluateCriterionInterface $evaluateCriterionWithoutFeature,
        EvaluateCriterionInterface $evaluateCriterionWhateverFeature,
        EvaluateCriterionInterface $evaluateCriterionAllFeatureOnly,
    ) {
        $this->beConstructedWith($allCriteriaFeature);

        $this->criterionCodeWithoutFeature = new CriterionCode('criterion_without_feature');
        $this->criterionCodeWhateverFeature = new CriterionCode('criterion_whatever_feature');
        $this->criterionCodeAllFeatureOnly = new CriterionCode('criterion_all_feature');

        $evaluateCriterionWithoutFeature->getCode()->willReturn($this->criterionCodeWithoutFeature);
        $evaluateCriterionWhateverFeature->getCode()->willReturn($this->criterionCodeWhateverFeature);
        $evaluateCriterionAllFeatureOnly->getCode()->willReturn($this->criterionCodeAllFeatureOnly);

        $this->register($evaluateCriterionWithoutFeature, null);
        $this->register($evaluateCriterionWhateverFeature, 'whatever_feature');
        $this->register($evaluateCriterionAllFeatureOnly, 'data_quality_insights_all_criteria');
    }

    public function it_gets_criteria_codes_with_all_criteria_feature_enabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(true);

        $this->getEnabledCriterionCodes()->shouldReturn([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly]);
        $this->getAllCriterionCodes()->shouldReturn([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly]);
        $this->getPartialCriterionCodes()->shouldReturn([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature]);
    }

    public function it_gets_criteria_codes_with_all_criteria_feature_disabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(false);

        $this->getEnabledCriterionCodes()->shouldReturn([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature]);
        $this->getAllCriterionCodes()->shouldReturn([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly]);
        $this->getPartialCriterionCodes()->shouldReturn([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature]);
    }
}
