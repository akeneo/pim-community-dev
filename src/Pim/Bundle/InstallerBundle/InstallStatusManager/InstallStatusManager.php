<?php

declare(strict_types=1);

namespace Pim\Bundle\InstallerBundle\InstallStatusManager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Exception\ConnectionException;

/**
 * InstallStatusManager : Check that PIM has been installed by checking that an 'oro_user' table exists.
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatusManager
{
    public const INSTALL_TABLE_NAME ='oro_user';
    public const MYSQL_META_COLUMN_CREATE_TIME ='create_time';

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
     * @return string return null if the PIM not installed or return the timestamp of creation of the 'oro_user' table.
     */
    public function getInstalledFlag() : ?string
    {
        $sql = 'SELECT create_time FROM INFORMATION_SCHEMA.TABLES
                WHERE table_schema = :database_name
                AND table_name = :install_table_name ';

        $connection = $this->doctrine->getConnection();
        try {
            $stmt = $connection->prepare($sql);
        } catch (ConnectionException $e) {
            return null;
        }

        $stmt->bindValue('database_name', $this->databaseName);
        $stmt->bindValue('install_table_name', self::INSTALL_TABLE_NAME);
        $stmt->execute();

        return $stmt->fetch()[self::MYSQL_META_COLUMN_CREATE_TIME];
    }

    /**
    * @return bool Return a boolean about installation state of the PIM
    */
    public function isInstalled() : bool
    {
        return null !== $this->getInstalledFlag();
    }
}
