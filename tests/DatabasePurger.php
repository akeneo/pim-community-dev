<?php

namespace Akeneo\Test\Integration;

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
class DatabasePurger
{
    /** @var KernelInterface */
    protected $kernel;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
    }

    /**
     * Calls the appropriates purgers depending on the storage.
     */
    public function purge()
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--env' => 'test',
            '--force' => true,
        ]);
        $output = new BufferedOutput();

        $exitCode = $application->run($input, $output);

        if (0 !== $exitCode) {
            throw new \Exception(sprintf('Impossible to purge the database! "%s"', $output->fetch()));
        }
    }
}
