<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence;

interface DeleteConnectedAppQueryInterface
{
    public function execute(string $appId): void;
}
