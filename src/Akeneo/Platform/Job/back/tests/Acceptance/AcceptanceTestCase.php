<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AcceptanceTestCase extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false, 'environment' => 'test_fake']);
    }

    protected function get(string $service): ?object
    {
        return self::getContainer()->get($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->ensureKernelShutdown();
    }
}
