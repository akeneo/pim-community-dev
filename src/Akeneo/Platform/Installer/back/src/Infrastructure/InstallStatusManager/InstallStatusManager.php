<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\InstallStatusManager;

use Akeneo\Platform\Installer\Infrastructure\Persistence\Sql\GetInstallDatetime;
use Akeneo\Platform\Installer\Infrastructure\Persistence\Sql\GetResetEvents;
use Doctrine\DBAL\Exception\TableNotFoundException;

/**
 * Checks whether the PIM has already been installed.
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatusManager
{
    public function __construct(
        private readonly GetInstallDatetime $installDatetimeQuery,
        private readonly GetResetEvents $getResetEvents,
    ) {
    }

    /**
     * Returns null if the PIM not installed or returns the timestamp of creation of the 'pim_user' table.
     * Definition of PIM not installed:
     * - no 'install_data' value in pim_configuration table
     * - no pim_configuration table at all (happens at first install).
     */
    public function getPimInstallDateTime(): ?\DateTime
    {
        try {
            $installDatetime = ($this->installDatetimeQuery)();
        } catch (TableNotFoundException) {
            return null;
        }

        return $installDatetime;
    }

    public function getPimResetEvents(): array
    {
        try {
            $resetEvents = ($this->getResetEvents)();
        } catch (TableNotFoundException) {
            return [];
        }

        return $resetEvents;
    }

    public function isPimInstalled(): bool
    {
        return null !== $this->getPimInstallDateTime();
    }
}
