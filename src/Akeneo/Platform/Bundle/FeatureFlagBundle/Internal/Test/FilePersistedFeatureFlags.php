<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;

/**
 * Not for production.
 *
 * Registry of feature flags. All existing feature flag are deactivated by default. A feature flag can be changed after Symfony container boot.
 * Enabled feature flag are persisted in file. The goal of this class is to share a state of the enabled feature flags across multiple processes.
 *
 * The main purpose is for end to end test with web browsers. In these cases, feature flags should be enabled before starting the test itself. Then, all HTTP requests will share the same feature flag configuration.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilePersistedFeatureFlags implements FeatureFlags
{
    public const FILENAME = 'feature_flag.json';

    private string $filepath;

    public function __construct(private Registry $registry, private string $featureFlagDir)
    {
        $this->filepath = $this->featureFlagDir . self::FILENAME;
    }

    public function enable(string $feature): void
    {
        $this->throwExceptionIfFlagDoesNotExist($feature);

        $enabledFeatures = file_exists($this->filepath) ? json_decode(file_get_contents($this->filepath), true) : [];
        $enabledFeatures[$feature] = true;
        file_put_contents($this->filepath, json_encode($enabledFeatures));
    }

    public function disable(string $feature): void
    {
        if (!file_exists($this->filepath)) {
            return;
        }

        $enabledFeatures = json_decode(file_get_contents($this->filepath), true);
        unset($enabledFeatures[$feature]);
        file_put_contents($this->filepath, json_encode($enabledFeatures));
    }

    public function isEnabled($feature): bool
    {
        $this->throwExceptionIfFlagDoesNotExist($feature);
        $content = file_exists($this->filepath) ? json_decode(file_get_contents($this->filepath), true) : [];

        return isset($content[$feature]);
    }

    public function all(): array
    {
        $featureFlagNames = array_keys($this->registry->all());
        $enabledFeatures = file_exists($this->filepath) ? json_decode(file_get_contents($this->filepath), true) : [];

        return array_merge(
            array_fill_keys($featureFlagNames, false),
            $enabledFeatures
        );
    }

    public function deleteFile(): void
    {
        if (file_exists($this->filepath)) {
            unlink($this->filepath);
        }
    }

    /**
     * @param string $feature
     */
    private function throwExceptionIfFlagDoesNotExist(string $feature): void
    {
        $this->registry->get($feature);
    }
}
