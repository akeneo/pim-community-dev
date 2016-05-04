<?php

namespace Pim\Bundle\AnalyticsBundle\Provider;

/**
 * Returns the version of the storage used on the server.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StorageVersionProvider
{
    /** @const string */
    const DIVERGENT_MARIADB_VERSION = '10';

    /** @var string */
    protected $mongoDatabase;

    /** @var string */
    protected $mongoServer;

    /**
     * @param string $mongoServer
     * @param string $mongoDatabase
     */
    public function __construct($mongoServer = null, $mongoDatabase = null)
    {
        $this->mongoServer   = $mongoServer;
        $this->mongoDatabase = $mongoDatabase;
    }

    /**
     * Provides MySQL/MariaDB version and, if used, MongoDB version.
     *
     * @return array
     */
    public function provide()
    {
        $version = $this->getSQLVersion();

        if (null !== $this->mongoServer && null !== $this->mongoDatabase) {
            array_merge(
                $version,
                $this->getMongoDBVersion()
            );
        }

        return $version;
    }

    /**
     * Returns the version of MySQL or MariaDB.
     *
     * @return array
     */
    protected function getSQLVersion()
    {
        $version = mysql_get_client_info();

        if (true === version_compare($version, static::DIVERGENT_MARIADB_VERSION, '>=')) {
            $storage = 'mariadb_version';
        } else {
            $storage = 'mysql_version';
        }

        return [$storage => $version];
    }

    /**
     * Returns the version of MongoDB, if used.
     *
     * @return array
     */
    protected function getMongoDBVersion()
    {
        $client   = new \MongoClient($this->mongoServer);
        $database = $this->mongoDatabase;

        $mongo       = new \MongoDB($client, $database);
        $mongodbInfo = $mongo->command(['serverStatus' => true]);

        return ['mongodb_version' => $mongodbInfo['version']];
    }
}
