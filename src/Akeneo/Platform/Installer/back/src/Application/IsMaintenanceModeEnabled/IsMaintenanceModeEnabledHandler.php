<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\IsMaintenanceModeEnabled;

use Akeneo\Platform\Installer\Domain\Query\IsMaintenanceModeEnabledInterface;

final class IsMaintenanceModeEnabledHandler
{
    public function __construct(
        private readonly IsMaintenanceModeEnabledInterface $isMaintenanceModeEnabled,
    ) {
    }

    public function handle(): bool
    {
        return $this->isMaintenanceModeEnabled->execute();
    }
}
