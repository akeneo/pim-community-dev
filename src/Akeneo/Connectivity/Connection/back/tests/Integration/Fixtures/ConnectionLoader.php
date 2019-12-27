<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionLoader
{
    /** @var CreateConnectionHandler*/
    private $createConnectionHandler;

    public function __construct(CreateConnectionHandler $createConnectionHandler)
    {
        $this->createConnectionHandler = $createConnectionHandler;
    }

    public function createConnection(string $code, string $label, string $flowType): ConnectionWithCredentials
    {
        $command = new CreateConnectionCommand($code, $label, $flowType);
        return $this->createConnectionHandler->handle($command);
    }
}
