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
    protected $tablesToDelete;

    /** @var string[] */
    protected $tablesToTruncate;

    /**
     * Purging the database with a delete is by an order of magnitude faster than a truncate on a lot of small tables.
     * Therefore, choose carefully the tables you want to truncate.
     *
     * @param Connection $connection       The connection to use
     * @param string[]   $tablesToDelete   The tables to purge with a delete in database, the order is not significant
     * @param string[]   $tablesToTruncate The tables to purge with a truncate in database, the order is not significant
     */
    public function __construct(Connection $connection, array $tablesToDelete, array $tablesToTruncate = [])
    {
        $this->connection = $connection;
        $this->tablesToDelete = $tablesToDelete;
        $this->tablesToTruncate = $tablesToTruncate;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;';

        foreach ($this->tablesToDelete as $table) {
            $sql .= sprintf('DELETE FROM %s ;', $table);
        }

        foreach ($this->tablesToTruncate as $table) {
            $sql .= sprintf('TRUNCATE TABLE %s;', $table);
        }

        // this query can fail without triggering any error, if a table does not exist
        $this->connection->exec($sql);
        $this->connection->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}
