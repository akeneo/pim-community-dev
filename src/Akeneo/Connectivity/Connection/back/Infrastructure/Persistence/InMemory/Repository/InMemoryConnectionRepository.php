<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemoryConnectionRepository implements ConnectionRepository
{
    public $dataRows = [];

    public function create(Connection $connection): void
    {
        $this->dataRows[(string) $connection->code()] = [
            'code' => (string) $connection->code(),
            'label' => (string) $connection->label(),
            'flow_type' => (string) $connection->flowType(),
            'client_id' => $connection->clientId()->id(),
            'user_id' => $connection->userId()->id(),
            'random_id' => uniqid(),
            'secret' => uniqid(),
            'username' => (string) $connection->code(),
            'password' => uniqid(),
            'image' => null,
        ];
    }

    public function findOneByCode(string $code): ?Connection
    {
        if (!isset($this->dataRows[$code])) {
            return null;
        }
        $dataRow = $this->dataRows[$code];

        return new Connection(
            $dataRow['code'],
            $dataRow['label'],
            $dataRow['flow_type'],
            $dataRow['client_id'],
            new UserId($dataRow['user_id']),
            $dataRow['image']
        );
    }

    public function update(Connection $connection): void
    {
        if (!isset($this->dataRows[(string) $connection->code()])) {
            throw new \LogicException(sprintf('Connection "%s" never persisted!', (string) $connection->code()));
        }

        $this->dataRows[(string) $connection->code()]['label'] = (string) $connection->label();
        $this->dataRows[(string) $connection->code()]['flow_type'] = (string) $connection->flowType();
        $this->dataRows[(string) $connection->code()]['image'] = null !== $connection->image() ? (string) $connection->image() : null;
    }

    public function delete(Connection $connection): void
    {
        if (isset($this->dataRows[(string) $connection->code()])) {
            unset($this->dataRows[(string) $connection->code()]);
        }
    }

    public function count(): int
    {
        return count($this->dataRows);
    }
}
