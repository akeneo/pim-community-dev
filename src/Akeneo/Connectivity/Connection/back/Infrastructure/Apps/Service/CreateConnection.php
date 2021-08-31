<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnection implements CreateConnectionInterface
{
    private ConnectionRepository $repository;
    private FindAConnectionHandler $findAConnectionHandler;

    public function __construct(
        ConnectionRepository $repository,
        FindAConnectionHandler $findAConnectionHandler
    ) {
        $this->repository = $repository;
        $this->findAConnectionHandler = $findAConnectionHandler;
    }

    public function execute(
        string $code,
        string $label,
        string $flowType,
        int $clientId,
        int $userId): ConnectionWithCredentials
    {
        $connection = new Connection(
            $code,
            $label,
            $flowType,
            $clientId,
            $userId
        );
        $this->repository->create($connection);

        $query = new FindAConnectionQuery((string) $connection->code());
        $connectionDTO = $this->findAConnectionHandler->handle($query);
        if (null === $connectionDTO) {
            throw new \RuntimeException();
        }

        return $connectionDTO;
    }
}
