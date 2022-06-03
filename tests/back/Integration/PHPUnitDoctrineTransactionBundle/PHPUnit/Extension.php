<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\PHPUnit;

use Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine\StaticRegistry;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

class Extension implements BeforeTestHook, AfterTestHook, BeforeFirstTestHook, AfterLastTestHook
{
    private ?bool $enabled;

    private function isExperimentalTestDatabaseEnabled(): bool
    {
        return $this->enabled ??= (bool) getenv('EXPERIMENTAL_TEST_DATABASE');
    }

    public function executeBeforeFirstTest(): void
    {
        if ($this->isExperimentalTestDatabaseEnabled()) {
            StaticRegistry::enable();
        }
    }

    public function executeBeforeTest(string $test): void
    {
        if ($this->isExperimentalTestDatabaseEnabled()) {
            StaticRegistry::beginTransaction();
        }
    }

    public function executeAfterTest(string $test, float $time): void
    {
        if ($this->isExperimentalTestDatabaseEnabled()) {
            StaticRegistry::rollBack();
        }
    }

    public function executeAfterLastTest(): void
    {
        if ($this->isExperimentalTestDatabaseEnabled()) {
            StaticRegistry::disable();
        }
    }
}
