<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Performance;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Blackfire\Profile\Configuration;

class PerformanceTestCase extends IntegrationTestCase
{
    use TestCaseTrait;

    protected Configuration $config;

    protected function setUp(): void
    {
        parent::setUp();

        $clientConfiguration = $this->getBlackfireClientConfiguration();
        $clientConfiguration->setEnv('CI');

        $this->config = new Configuration();
    }
}
