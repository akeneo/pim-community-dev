<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\Service;

use Doctrine\DBAL\Connection;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\DatabaseTableTrait;
use Symfony\Component\Lock\Store\DoctrineDbalStore;

class PimLockStore implements PersistingStoreInterface
{
    use DatabaseTableTrait;

    private DoctrineDbalStore $dbalStore;

    public function __construct(private readonly Connection $connection)
    {
        $this->dbalStore = new DoctrineDbalStore($this->connection);
    }

    public function save(Key $key): void
    {
        $this->dbalStore->save($key);
    }

    public function delete(Key $key): void
    {
        $this->connection->delete($this->table, [
            $this->idCol => $this->getHashedKey($key),
        ]);
    }

    public function exists(Key $key): bool
    {
        return $this->dbalStore->exists($key);
    }

    public function putOffExpiration(Key $key, float $ttl): void
    {
        $this->dbalStore->putOffExpiration($key, $ttl);
    }
}
