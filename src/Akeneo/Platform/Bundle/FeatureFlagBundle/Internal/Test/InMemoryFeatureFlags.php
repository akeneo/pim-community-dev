<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;

/**
 * Not for production.

 * Registry of in memory feature flags. All existing feature flag are deactivated by default.
 **
 * A feature flag can be changed after Symfony container boot. It is mainly used for testing purpose. Its configuration shares the same lifecycle as the Symfony container. Therefore, it cannot be used to configure feature flags in sub processes, or for end to end testing with multiple HTTP requests.
 *
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFeatureFlags implements FeatureFlags
{
    private array $flags = [];

    public function __construct(private Registry $registry)
    {
        $featureFlagNames = array_keys($this->registry->all());
        $this->flags = array_fill_keys($featureFlagNames, false);
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

        return $this->flags[$feature] ?? false;
    }

    public function all(): array
    {
        return $this->flags;
    }

    /**
     * No-op for compatibility with TestCase base class.
     * In-memory flags don't persist to file, so nothing to delete.
     */
    public function deleteFile(): void
    {
        // No-op: in-memory flags have no file to delete
    }

    private function throwExceptionIfFlagDoesNotExist(string $feature): void
    {
        $this->registry->get($feature);
    }
}
