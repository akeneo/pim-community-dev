<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DataQualityInsightsUcsEventFeature implements FeatureFlag
{
    public function __construct(private readonly mixed $envVar)
    {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return (bool) $this->envVar;
    }
}
