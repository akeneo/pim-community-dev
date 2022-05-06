<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;

interface CreateConnectedAppQueryInterface
{
    public function execute(ConnectedApp $app): void;
}
