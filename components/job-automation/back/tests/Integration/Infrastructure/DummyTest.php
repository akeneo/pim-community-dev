<?php

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure;

use Akeneo\Platform\JobAutomation\Infrastructure\Dummy;
use Akeneo\Platform\JobAutomation\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class DummyTest extends IntegrationTestCase
{
    public function test_it_returns_message(): void
    {
        $dummy = new Dummy('Hello world!');
        $this->assertEquals('Hello world!', $dummy->getMessage());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
