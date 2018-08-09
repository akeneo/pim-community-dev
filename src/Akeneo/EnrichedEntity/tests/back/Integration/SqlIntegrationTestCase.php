<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used for running integration tests testing the SQL implementation of query functions and repositories.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SqlIntegrationTestCase extends KernelTestCase
{
    /** @var KernelInterface */
    protected $testKernel;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->bootTestKernel();
    }

    protected function bootTestKernel(): void
    {
        $this->testKernel = new \AppKernelTest('test', false);
        $this->testKernel->boot();
    }

    /*
     * @return mixed
     */
    protected function get(string $service)
    {
        return $this->testKernel->getContainer()->get($service);
    }
}
