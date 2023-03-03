<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

class CheckSymfonyCommandsDefaultNameIntegration extends KernelTestCase
{
    public function testAllSymfonyCommandsHaveADefaultName(): void
    {
        $container = static::getContainer();

        foreach ($container->getServiceIds() as $serviceId) {
            try {
                $service = $container->get($serviceId);
            } catch (\Throwable $e) {
                $service = null;
            }

            if (null !== $service && \is_subclass_of($service, Command::class)) {
                Assert::assertNotNull($service::getDefaultName(), sprintf('The Symfony command "%s" must have a default name', $serviceId));
            }
        }
    }
}
