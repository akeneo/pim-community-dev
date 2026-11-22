<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\EntityManager;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Decorator\EntityManagerDecorator;

/**
 * This EntityManager aims to ensure that the connection is always open before doing an operation.
 *
 * It is useful in the context of long running processes, when the connection could have been closed
 * by the database due to a time out.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PersistedConnectionEntityManager extends EntityManagerDecorator
{
    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        $this->checkConnection();

        return $this->wrapped->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function flush($entity = null)
    {
        $this->checkConnection();

        return $this->wrapped->flush($entity);
    }

    /**
     * Ping the Server, if the connection is closed, it re-opens it automatically.
     *
     * DBAL 3 removed Connection::ping(): a dummy query is run instead, as recommended by DBAL. Like the former
     * ping(), any DBAL error means the connection is unusable: only MySQL error 2006 is mapped to ConnectionLost,
     * so a narrower catch would let e.g. error 2013 (lost connection during query) crash long-running jobs.
     */
    private function checkConnection(): void
    {
        $connection = $this->wrapped->getConnection();

        try {
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL());
        } catch (DBALException) {
            $connection->close();
            $connection->connect();
        }
    }
}
