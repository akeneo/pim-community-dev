<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * Registry of in memory feature flags. All existing feature flag are deactivated by default.
 *
 * It can be changed after Symfony container boot. It is mainly used for testing purpose. Its configuration shares the same lifecycle as the Symofony container. Therefore, it cannot be used to configure feature flags in sub processes, or for end to end testing with multiple HTTP requests.
 *
 * By default, a feature is disabled.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFeatureFlags implements FeatureFlags
{
    private array $flags = [];

    public function __construct(private Registry $registry)
    {
    }

    public function enable(string $feature): void
    {
        $this->throwExceptionIfFlagDoesNotExist($feature);

        $this->flags[$feature] = true;
    }

    public function disable(string $feature): void
    {
        $this->throwExceptionIfFlagDoesNotExist($feature);

        $this->flags[$feature] = false;
    }

    public function isEnabled($feature): bool
    {
        $this->throwExceptionIfFlagDoesNotExist($feature);

        // TIP-1561: onboarder feature flag would deactivated by default if we don't do that
        // the issue is that it makes the onboarder acceptance tests fail, but to fix it these tests we need to merge this PR
        // so this is a temporary dirty workaround to be able to fix and release onboarder, before removing it
        if ($feature === 'onboarder') {
            return true;
        }

        return $this->flags[$feature] ?? false;
    }

    /**
     * @param string $feature
     */
    private function throwExceptionIfFlagDoesNotExist(string $feature): void
    {
        $this->registry->get($feature);
    }
}
