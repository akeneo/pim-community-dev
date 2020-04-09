<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContext
{
    /** @var AreCredentialsValidCombinationQuery */
    private $areCredentialsValidCombinationQuery;

    /** @var SelectConnectionCodeByClientIdQuery */
    private $selectConnectionCodeByClientIdQuery;

    /** @var ConnectionRepository */
    private $connectionRepository;

    /** @var string */
    private $clientId;

    /** @var string */
    private $username;

    /** @var Connection */
    private $connection;

    /** @var bool */
    private $collectable;

    /** @var bool */
    private $areCredentialsValidCombination;

    public function __construct(
        AreCredentialsValidCombinationQuery $areCredentialsValidCombinationQuery,
        SelectConnectionCodeByClientIdQuery $selectConnectionCodeByClientIdQuery,
        ConnectionRepository $connectionRepository
    ) {
        $this->areCredentialsValidCombinationQuery = $areCredentialsValidCombinationQuery;
        $this->selectConnectionCodeByClientIdQuery = $selectConnectionCodeByClientIdQuery;
        $this->connectionRepository = $connectionRepository;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getConnection(): Connection
    {
        if (null !== $this->connection) {
            return $this->connection;
        }

        $connectionCode = $this->selectConnectionCodeByClientIdQuery->execute($this->clientId);
        $this->connection = $this->connectionRepository->findOneByCode($connectionCode);

        return $this->connection;
    }

    public function isCollectable(): bool
    {
        if (null !== $this->collectable) {
            return $this->collectable;
        }

        $this->collectable = $this->getConnection()->auditable() && $this->areCredentialsValidCombination();

        return $this->collectable;
    }

    public function areCredentialsValidCombination(): bool
    {
        if (null !== $this->areCredentialsValidCombination) {
            return $this->areCredentialsValidCombination;
        }

        $this->areCredentialsValidCombination = $this->areCredentialsValidCombinationQuery
            ->execute($this->clientId, $this->username);

        return $this->areCredentialsValidCombination;
    }
}
