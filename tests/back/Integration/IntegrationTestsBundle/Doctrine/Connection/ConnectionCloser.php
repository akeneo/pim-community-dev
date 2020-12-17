<?php

namespace Akeneo\Test\IntegrationTestsBundle\Doctrine\Connection;

use Doctrine\Persistence\ManagerRegistry;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionCloser
{
    protected ManagerRegistry $doctrineRegistry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->doctrineRegistry = $registry;
    }

    /**
     * Ensures that all used connections are well closed.
     *
     * @see https://github.com/akeneo/pim-community-dev/pull/5484
     */
    public function closeConnections()
    {
        foreach ($this->doctrineRegistry->getConnections() as $connection) {
            $connection->close();
        }
    }
}
