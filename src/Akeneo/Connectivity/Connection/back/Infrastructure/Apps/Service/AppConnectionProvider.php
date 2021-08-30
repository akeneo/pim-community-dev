<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\AppConnectionProviderInterface;
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
class AppConnectionProvider implements AppConnectionProviderInterface
{
    private ConnectionRepository $repository;
    private ClientProviderInterface $clientProvider;
    private FindAConnectionHandler $findAConnectionHandler;

    public function __construct(
        ConnectionRepository $repository,
        ClientProviderInterface $clientProvider,
        FindAConnectionHandler $findAConnectionHandler
    ) {
        $this->repository = $repository;
        $this->clientProvider = $clientProvider;
        $this->findAConnectionHandler = $findAConnectionHandler;
    }

    public function createAppConnection(string $appName, string $appId, int $userId): ConnectionWithCredentials
    {
        $client = $this->clientProvider->findClientByAppId($appId);
        if (null === $client) {
            throw new \RuntimeException("No client found with client id $appId");
        }

        $connection = new Connection(
            $appId,
            $appName,
            FlowType::OTHER,
            $client->getId(),
            $userId,
            null
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
