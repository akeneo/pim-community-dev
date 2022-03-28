<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;

/**
 * Single entry point to determine if a feature is enabled or not.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FeatureFlags
{
    /**
     * @throws \InvalidArgumentException if the feature flag does not exist
     */
    public function isEnabled(string $feature): bool;

    /**
     * @return array<string, bool>
     */
    public function all(): array;
}
