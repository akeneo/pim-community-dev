<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * Collects MySQL version.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StorageDataCollector implements DataCollectorInterface
{
    /** @var string */
    protected $dbHost;

    /** @var int */
    protected $dbPort;

    /** @var string */
    protected $dbName;

    /** @var string */
    protected $dbUser;

    /** @var string */
    protected $dbPassword;

    /**
     * @param string $dbHost
     * @param string $dbPort
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPassword
     */
    public function __construct($dbHost, $dbPort, $dbName, $dbUser, $dbPassword)
    {
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $connection = new \PDO(
            sprintf('mysql:dbname=%s;host=%s;port=%d', $this->dbName, $this->dbHost, $this->dbPort),
            $this->dbUser,
            $this->dbPassword
        );

        return ['mysql_version' => $connection->getAttribute(\PDO::ATTR_SERVER_VERSION)];
    }
}
