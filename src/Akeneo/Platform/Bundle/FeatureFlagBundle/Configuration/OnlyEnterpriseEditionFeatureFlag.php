<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

final class OnlyEnterpriseEditionFeatureFlag implements FeatureFlag
{
    private const EDITIONS = [
        'flexibility_instance',
        'serenity_instance',
    ];

    public function __construct(
        private string $edition
    ) {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return in_array($this->edition, self::EDITIONS);
    }
}
