<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductKeyIndicatorsByFeatureRegistry
{
    private array $allKeyIndicatorCodes = [];
    private array $partialKeyIndicatorCodes = [];

    public function __construct(
        private FeatureFlag $allCriteriaFeature,
    ) {
    }

    public function register(ComputeProductsKeyIndicator $computeKeyIndicator, ?string $feature): void
    {
        $this->allKeyIndicatorCodes[] = $computeKeyIndicator->getCode();

        if ('data_quality_insights_all_criteria' !== $feature) {
            $this->partialKeyIndicatorCodes[] = $computeKeyIndicator->getCode();
        }
    }

    /**
     * @return array<KeyIndicatorCode>
     */
    public function getCodes(): array
    {
        return $this->allCriteriaFeature->isEnabled()
            ? $this->allKeyIndicatorCodes
            : $this->partialKeyIndicatorCodes;
    }
}
