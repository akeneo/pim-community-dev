<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence;

interface DeleteTestAppQueryInterface
{
    public function execute(string $clientId): void;
}
