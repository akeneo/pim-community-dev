<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Symfony\Component\Process\Process;

/**
 * Factory interface for creating Process instances.
 * Allows mocking process creation in unit tests.
 */
interface ProcessFactoryInterface
{
    /**
     * @param array<string> $command
     * @param array<string, string|false>|null $env
     */
    public function create(array $command, ?string $cwd = null, ?array $env = null): Process;
}
