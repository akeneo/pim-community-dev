<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionLoader
{
    public function __construct(
        private CreateConnectionHandler $createConnectionHandler,
        private UpdateConnectionHandler $updateConnectionHandler,
    ) {
    }

    public function createConnection(
        string $code,
        string $label,
        string $flowType,
        bool $auditable,
        ?string $type = null
    ): ConnectionWithCredentials {
        $command = new CreateConnectionCommand($code, $label, $flowType, $auditable, $type);

        return $this->createConnectionHandler->handle($command);
    }

    public function update(
        string $code,
        string $label,
        string $flowType,
        ?string $image,
        string $userRoleId,
        ?string $userGroupId,
        bool $auditable
    ): void {
        $command = new UpdateConnectionCommand($code, $label, $flowType, $image, $userRoleId, $userGroupId, $auditable);
        $this->updateConnectionHandler->handle($command);
    }
}
