<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\EntityManager;

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
     */
    private function checkConnection(): void
    {
        $connection = $this->wrapped->getConnection();

        if (false === $connection->ping()) {
            $connection->close();
            $connection->connect();
        }
    }
}
