<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence;

interface UpdateConnectedAppScopesQueryInterface
{
    /**
     * @param string[] $scopes
     */
    public function execute(array $scopes, string $appId): void;
}
