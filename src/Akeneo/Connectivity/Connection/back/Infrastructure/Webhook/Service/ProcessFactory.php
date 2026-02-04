<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Symfony\Component\Process\Process;

class ProcessFactory implements ProcessFactoryInterface
{
    public function create(array $command, ?string $cwd = null, ?array $env = null): Process
    {
        return new Process($command, $cwd, $env);
    }
}
