<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure;

use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQueryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContext implements ConnectionContextInterface
{
    private ?string $clientId = null;

    private ?string $username = null;

    private ?Connection $connection = null;

    private ?bool $collectable = null;

    private ?bool $areCredentialsValidCombination = null;

    public function __construct(private AreCredentialsValidCombinationQueryInterface $areCredentialsValidCombinationQuery, private SelectConnectionCodeByClientIdQueryInterface $selectConnectionCodeByClientIdQuery, private ConnectionRepositoryInterface $connectionRepository)
    {
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getConnection(): ?Connection
    {
        if (null !== $this->connection) {
            return $this->connection;
        }
        if (null === $this->clientId) {
            return null;
        }

        $connectionCode = $this->selectConnectionCodeByClientIdQuery->execute($this->clientId);

        if (null === $connectionCode) {
            return null;
        }

        return $this->connection = $this->connectionRepository->findOneByCode($connectionCode);
    }

    public function isCollectable(): bool
    {
        if (null !== $this->collectable) {
            return $this->collectable;
        }

        if (null === $this->getConnection()) {
            throw new \LogicException('You must initialize client id and username before using this service.');
        }

        return $this->collectable = $this->getConnection()->auditable() && $this->areCredentialsValidCombination();
    }

    public function areCredentialsValidCombination(): bool
    {
        if (null !== $this->areCredentialsValidCombination) {
            return $this->areCredentialsValidCombination;
        }

        if (null === $this->clientId || null === $this->username) {
            throw new \LogicException('You must initialize client id and username before using this service.');
        }

        $this->areCredentialsValidCombination = $this->areCredentialsValidCombinationQuery
            ->execute($this->clientId, $this->username);

        return $this->areCredentialsValidCombination;
    }
}
