<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used for running integration tests testing the web controllers.
 *
 * Every service definition of repositories or query functions uses the in memory implementation that manipulates
 * objects.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class ControllerIntegrationTestCase extends KernelTestCase
{
    /** @var KernelInterface */
    protected $testKernel;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->bootTestFakeKernel();
    }

    protected function get(string $service)
    {
        return $this->testKernel->getContainer()->get($service);
    }

    private function bootTestFakeKernel(): void
    {
        $this->testKernel = new \AppKernelTest('test_fake', false);
        $this->testKernel->boot();
    }
}
