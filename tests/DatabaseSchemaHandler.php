<?php

namespace Akeneo\Test\Integration;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabaseSchemaHandler
{
    /** @var KernelInterface */
    protected $kernel;

    /** @var ContainerInterface */
    protected $container;

    /** @var Application */
    protected $cli;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
        $this->cli = new Application($this->kernel);
        $this->cli->setAutoExit(false);
    }

    /**
     * Drop and recreate the database schema.
     *
     * @throws \RuntimeException
     */
    public function reset()
    {
        $this->drop();
        $this->create();

        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM ===
            $this->container->getParameter('pim_catalog_product_storage_driver')
        ) {
            $this->dropMongo();
            $this->createMongo();
        }
    }

    /**
     * Drop the database schema.
     *
     * @throws \RuntimeException
     */
    private function drop()
    {
        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--env' => 'test',
            '--force' => true,
        ]);
        $output = new BufferedOutput();

        $exitCode = $this->cli->run($input, $output);

        if (0 !== $exitCode) {
            throw new \RuntimeException(sprintf('Impossible to drop the database schema! "%s"', $output->fetch()));
        }
    }

    /**
     * Drop the MongoDB database schema.
     *
     * @throws \RuntimeException
     */
    private function dropMongo()
    {
        $input = new ArrayInput([
            'command' => 'doctrine:mongodb:schema:drop',
            '--env' => 'test',
        ]);
        $output = new BufferedOutput();

        $exitCode = $this->cli->run($input, $output);

        if (0 !== $exitCode) {
            throw new \RuntimeException(sprintf('Impossible to drop the MongoDB database schema! "%s"', $output->fetch()));
        }
    }

    /**
     * Create the database schema.
     *
     * @throws \RuntimeException
     */
    private function create()
    {
        $input = new ArrayInput([
            'command' => 'doctrine:schema:create',
            '--env' => 'test',
        ]);
        $output = new BufferedOutput();

        $exitCode = $this->cli->run($input, $output);

        if (0 !== $exitCode) {
            throw new \RuntimeException(sprintf('Impossible to create the database schema! "%s"', $output->fetch()));
        }
    }

    /**
     * Create the MongoDB database schema.
     *
     * @throws \RuntimeException
     */
    private function createMongo()
    {
        $input = new ArrayInput([
            'command' => 'doctrine:mongodb:schema:create',
            '--env' => 'test',
        ]);
        $output = new BufferedOutput();

        $exitCode = $this->cli->run($input, $output);

        if (0 !== $exitCode) {
            throw new \RuntimeException(sprintf('Impossible to create the MongoDB database schema! "%s"', $output->fetch()));
        }
    }
}
