<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OnlySaasFeatureFlag implements FeatureFlag
{
    private const SAAS_EDITIONS = [
        'serenity_instance',
        'growth_edition_instance',
        'pim_trial_instance',
    ];

    public function __construct(private readonly string $edition)
    {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return \in_array($this->edition, self::SAAS_EDITIONS);
    }
}
