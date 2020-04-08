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
class FeatureFlags
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
}
