<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\IsMaintenanceModeEnabled;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Installer\Domain\Query\IsMaintenanceModeEnabledInterface;

final class IsMaintenanceModeEnabledHandler
{
    public function __construct(
        private readonly FeatureFlag $pimResetFeatureFlag,
        private readonly IsMaintenanceModeEnabledInterface $isMaintenanceModeEnabled,
    ) {
    }

    public function handle(): bool
    {
        if (getenv('MAINTENANCE_MODE_ENABLED') === '1') {
            return true;
        }

        if (!$this->pimResetFeatureFlag->isEnabled()) {
            return false;
        }

        return $this->isMaintenanceModeEnabled->execute();
    }
}
