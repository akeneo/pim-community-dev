<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration;

use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This class is used for running integration tests testing the web controllers.
 *
 * Every service definition of repositories or query functions uses the in memory implementation that manipulates
 * objects.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->bootTestKernel();
        $this->overrideSqlImplementationsForInMemoryImplementations();
    }

    protected function get(string $service)
    {
        return $this->testKernel->getContainer()->get($service);
    }

    private function bootTestKernel(): void
    {
        $this->testKernel = new \AppKernelTest('test', false);
        $this->testKernel->boot();
    }

    private function overrideSqlImplementationsForInMemoryImplementations(): void
    {
        $services = Yaml::parseFile(__DIR__ . '/controller_integration_services.yml');
        foreach ($services['services'] as $serviceId => $fqcn) {
            $arguments = $this->getArgumentsAsServices($fqcn['arguments'] ?? []);
            $reflector = new ReflectionClass($fqcn['class']);
            $this->testKernel->getContainer()->set($serviceId, $reflector->newInstanceArgs($arguments));
        }
    }

    private function getArgumentsAsServices(array $arguments): array
    {
        $services = [];
        foreach ($arguments as $dependency) {
            $services[] = $this->get(ltrim($dependency, '@'));
        }

        return $services;
    }
}
