<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\ServiceApi\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectedAppRemover
{
    public function __construct(
        private DeleteAppHandler $deleteAppHandler,
    ) {
    }

    public function remove(string $appId): void
    {
        $this->deleteAppHandler->handle(new DeleteAppCommand($appId));
    }
}
