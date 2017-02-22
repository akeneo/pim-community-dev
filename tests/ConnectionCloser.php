<?php

namespace Akeneo\Test\Integration;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionCloser
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Ensures that all used connections are well closed.
     *
     * @see https://github.com/akeneo/pim-community-dev/pull/5484
     */
    public function closeConnections()
    {
        $doctrine = $this->container->get('doctrine');
        foreach ($doctrine->getConnections() as $connection) {
            $connection->close();
        }
    }
}
