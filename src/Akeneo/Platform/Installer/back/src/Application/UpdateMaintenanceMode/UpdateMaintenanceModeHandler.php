<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\UpdateMaintenanceMode;

use Akeneo\Platform\Installer\Domain\Query\UpdateMaintenanceModeInterface;

final class UpdateMaintenanceModeHandler
{
    public function __construct(
        private readonly UpdateMaintenanceModeInterface $updateMaintenanceMode,
    ) {
    }

    public function handle(UpdateMaintenanceModeCommand $command): void
    {
        $this->updateMaintenanceMode->execute($command->isEnabled);
    }
}
