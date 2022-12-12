<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class InstallContextFeatureFlag implements FeatureFlag
{
    private bool $isEnabled = false;

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function disable(): void
    {
        $this->isEnabled = false;
    }

    public function enable(): void
    {
        $this->isEnabled = true;
    }
}
