<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DataQualityInsightsFeature implements FeatureFlag
{
    private $activationFlag;

    public function __construct(bool $activationFlag)
    {
        $this->activationFlag = $activationFlag;
    }

    public function isEnabled(): bool
    {
        return (true === $this->activationFlag);
    }
}
