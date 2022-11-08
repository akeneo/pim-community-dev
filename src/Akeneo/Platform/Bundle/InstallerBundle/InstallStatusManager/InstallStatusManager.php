<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager;

use Akeneo\Platform\Bundle\InstallerBundle\Exception\UnavailableCreationTimeException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Exception\ConnectionException;

/**
 * Checks whether the PIM has already been installed by checking that an 'pim_user' table exists.
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatusManager
{
    public const INSTALL_TABLE_NAME ='oro_user';
    public const MYSQL_META_COLUMN_CREATE_TIME ='CREATE_TIME';

    /**
     * @var Registry $doctrine
     */
    protected $doctrine;

    /**
     * @var string $databaseName
     */
    protected $databaseName;

    /**
     * @param Registry $doctrine
     * @param string string $databaseName
     */
    public function __construct(Registry $doctrine, string $databaseName)
    {
        $this->doctrine = $doctrine;
        $this->databaseName = $databaseName;
    }

    /**
     * Returns null if the PIM not installed or returns the timestamp of creation of the 'pim_user' table.
     *
     * @return \DateTime
     */
    public function getPimInstallDateTime() : ?\DateTime
    {
        $sql = 'SELECT create_time FROM INFORMATION_SCHEMA.TABLES
                WHERE table_schema = :database_name
                AND table_name = :install_table_name';

        $connection = $this->doctrine->getConnection();
        try {
            $stmt = $connection->prepare($sql);
        } catch (ConnectionException $e) {
            throw new UnavailableCreationTimeException('Database connection failed.', $e);
        }

        $stmt->bindValue('database_name', $this->databaseName);
        $stmt->bindValue('install_table_name', self::INSTALL_TABLE_NAME);
        $stmt->execute();

        $result = $stmt->fetch();
        if (!isset($result[self::MYSQL_META_COLUMN_CREATE_TIME])) {
            throw new UnavailableCreationTimeException(
                sprintf(
                    '"%s" not available for table "%s"',
                    self::MYSQL_META_COLUMN_CREATE_TIME,
                    self::INSTALL_TABLE_NAME
                )
            );
        }
        $installDateTime = new \DateTime($result[self::MYSQL_META_COLUMN_CREATE_TIME]);

        return $installDateTime;
    }

    /**
    * @return bool Return a boolean about installation state of the PIM
    */
    public function isPimInstalled() : bool
    {
        try {
            $this->getPimInstallDateTime();
        } catch (UnavailableCreationTimeException $e) {
            return false;
        }

        return true;
    }
}
