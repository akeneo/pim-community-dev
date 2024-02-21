<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence;

interface DeleteCustomAppQueryInterface
{
    public function execute(string $clientId): void;
}
