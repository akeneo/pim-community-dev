<?php

namespace Pim\Behat\Context;

use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\DBAL\Connection;

/**
 * Purger that use a Doctrine DBAL connection to purge several tables at a time.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DBALPurger implements PurgerInterface
{
    /** @var Connection */
    protected $connection;

    /** @var string[] */
    protected $tables;

    /**
     * @param Connection $connection The connection to use
     * @param string[]   $tables     The tables to purge, the order is significant
     */
    public function __construct(Connection $connection, array $tables)
    {
        $this->connection = $connection;
        $this->tables     = $tables;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        foreach ($this->tables as $table) {
            $sql = 'DELETE FROM ' . $table;
            $this->connection->exec($sql);
        }
    }
}
