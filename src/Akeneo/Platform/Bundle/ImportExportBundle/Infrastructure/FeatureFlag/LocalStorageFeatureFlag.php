<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\OnlyFlexibilityOnPremiseFeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\InstallerBundle\FeatureFlag\InstallContextFeatureFlag;

class LocalStorageFeatureFlag implements FeatureFlag
{
    public function __construct(
        private OnlyFlexibilityOnPremiseFeatureFlag $onlyFlexibilityOnPremiseFeatureFlag,
        private InstallContextFeatureFlag $installContextFeature,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->onlyFlexibilityOnPremiseFeatureFlag->isEnabled()
            || $this->installContextFeature->isEnabled();
    }
}
