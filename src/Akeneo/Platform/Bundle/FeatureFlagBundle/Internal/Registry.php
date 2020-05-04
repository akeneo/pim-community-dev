<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use InvalidArgumentException;

/**
 * Registry that holds all feature flags.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Registry
{
    /** @var FeatureFlag[] */
    private $flags = [];

    public function add(string $feature, FeatureFlag $flag)
    {
        $this->flags[$feature] = $flag;
    }

    public function get(string $feature): FeatureFlag
    {
        if (!array_key_exists($feature, $this->flags)) {
            throw new InvalidArgumentException(sprintf('No flag registered for feature "%s".', $feature));
        }

        return $this->flags[$feature];
    }

    public function all(): array
    {
        $featureFlags = [];
        foreach ($this->flags as $feature => $flag) {
            $featureFlags[$feature] = $flag->isEnabled();
        }

        return $featureFlags;
    }
}
