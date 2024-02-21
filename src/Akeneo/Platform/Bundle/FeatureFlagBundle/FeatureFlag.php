<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

/**
 * Defines if a feature is enabled or not. Allows any implementation. Be creative :)
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FeatureFlag
{
    public function isEnabled(?string $feature = null): bool;
}
