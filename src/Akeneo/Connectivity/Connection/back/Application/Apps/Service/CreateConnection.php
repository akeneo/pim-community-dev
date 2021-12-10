<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnection implements CreateConnectionInterface
{
    private const CONNECTION_TYPE_APP = 'app';

    private ConnectionRepository $repository;
    private SelectConnectionWithCredentialsByCodeQuery $selectConnectionWithCredentialsByCodeQuery;

    public function __construct(
        ConnectionRepository $repository,
        SelectConnectionWithCredentialsByCodeQuery $selectConnectionWithCredentialsByCodeQuery
    ) {
        $this->repository = $repository;
        $this->selectConnectionWithCredentialsByCodeQuery = $selectConnectionWithCredentialsByCodeQuery;
    }

    public function execute(
        string $code,
        string $label,
        string $flowType,
        int $clientId,
        int $userId
    ): ConnectionWithCredentials {
        $connection = new Connection(
            $code,
            $label,
            $flowType,
            $clientId,
            $userId,
            null,
            false,
            self::CONNECTION_TYPE_APP
        );

        $this->repository->create($connection);

        $connectionWithCredentials = $this->selectConnectionWithCredentialsByCodeQuery->execute($code);

        if (null === $connectionWithCredentials) {
            throw new \LogicException('The connection just created should be available, it is not.');
        }

        return $connectionWithCredentials;
    }
}
