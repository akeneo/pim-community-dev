<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Feature
{
    public const PERMISSION = 'permission';

    public function __construct(private FeatureFlags $featureFlags)
    {
    }

    public function isEnabled(string $name): bool
    {
        // TODO: We should always use the FeatureFlags service to check that.
        if ($name === self::PERMISSION) {
            return \class_exists('Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle');
        }

        return $this->featureFlags->isEnabled($name);
    }
}
