<?php

namespace Pim\Behat\Context;

use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\DBAL\Connection;

/**
 * Session purger from DB
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimplePurger implements PurgerInterface
{
    /** @var Connection */
    protected $connection;

    /** @var string[] */
    protected $tables;

    /**
     * @param Connection $connection
     * @param string[]   $tables
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
