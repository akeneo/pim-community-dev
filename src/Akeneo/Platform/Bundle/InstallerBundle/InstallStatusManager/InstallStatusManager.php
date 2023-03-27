<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager;

use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\GetInstallDatetime;
use Doctrine\DBAL\Exception;
use PDOException;

/**
 * Checks whether the PIM has already been installed by checking that an 'pim_user' table exists.
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatusManager
{
    public function __construct(protected GetInstallDatetime $installDatetimeQuery)
    {
    }

    /**
     * Returns null if the PIM not installed or returns the timestamp of creation of the 'pim_user' table.
     */
    public function getPimInstallDateTime(): ?\DateTime
    {
        try {
            $installDatetime = ($this->installDatetimeQuery)();
        } catch (Exception|PDOException $e) {
            return null;
        }

        return $installDatetime;
    }

    /**
     * @return bool Return a boolean about installation state of the PIM
     */
    public function isPimInstalled(): bool
    {
        return null !== $this->getPimInstallDateTime();
    }
}
