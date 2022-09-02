<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;

interface FindOneConnectedAppByConnectionCodeQueryInterface
{
    public function execute(string $connectionCode): ?ConnectedApp;
}
