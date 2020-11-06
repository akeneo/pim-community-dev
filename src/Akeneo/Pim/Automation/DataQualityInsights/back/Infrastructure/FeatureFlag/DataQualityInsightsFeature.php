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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;

final class DataQualityInsightsFeature implements FeatureFlag
{
    private $activationFlag;

    public function __construct(bool $activationFlag)
    {
        $this->activationFlag = $activationFlag;
    }

    public function isEnabled(): bool
    {
        return ($this->activationFlag);
    }
}
