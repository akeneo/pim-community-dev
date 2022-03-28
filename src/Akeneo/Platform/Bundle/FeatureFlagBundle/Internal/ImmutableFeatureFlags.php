<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * Registry of feature flags that cannot be changed after Symfony container boot.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImmutableFeatureFlags implements FeatureFlags
{
    /** @var Registry */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $feature
     *
     * @return bool
     */
    public function isEnabled(string $feature): bool
    {
        $flag = $this->registry->get($feature);

        return $flag->isEnabled();
    }

    public function all(): array
    {
        return $this->registry->all();
    }
}
