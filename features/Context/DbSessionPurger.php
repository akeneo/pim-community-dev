<?php

namespace Context;

use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\DBAL\Connection;

/**
 * Session purger from DB
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbSessionPurger implements PurgerInterface
{
    /** @var Connection */
    protected $connection;

    /** @var string */
    protected $sessionTable;

    /**
     * Construct new session purger instance.
     *
     * @param Connection $connection
     * @param string     $sesionTable
     */
    public function __construct(Connection $connection, $sessionTable)
    {
        $this->connection   = $connection;
        $this->sessionTable = $sessionTable;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        $truncateSql = sprintf("TRUNCATE %s", $this->sessionTable);
        $this->connection->exec($truncateSql);
    }
}
