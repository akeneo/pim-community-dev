<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\ParameterType;

class StaticConnection implements Driver\Connection
{
    private Driver\Connection $decorated;

    public function __construct(
        Driver\Connection $decorated
    ) {
        $this->decorated = $decorated;
    }

    /**
     * @return Driver\Statement<mixed>
     */
    public function prepare($sql): Driver\Statement
    {
        return $this->decorated->prepare($sql);
    }

    /**
     * @return Driver\Statement<mixed>
     */
    public function query(): Driver\Statement
    {
        return call_user_func_array([$this->decorated, 'query'], func_get_args());
    }

    public function quote($value, $type = ParameterType::STRING): mixed
    {
        return $this->decorated->quote($value, $type);
    }

    public function exec($sql): int
    {
        return $this->decorated->exec($sql);
    }

    public function lastInsertId($name = null): string
    {
        return $this->decorated->lastInsertId($name);
    }

    public function beginTransaction(): bool
    {
        try {
            return $this->decorated->beginTransaction();
        } catch (\PDOException $e) {
            // we can ignore errors due to deep nested transactions
            return true;
        }
    }

    public function commit(): bool
    {
        return $this->decorated->commit();
    }

    public function rollBack(): bool
    {
        try {
            return $this->decorated->rollBack();
        } catch (\PDOException $e) {
            // we can ignore errors due to deep nested transactions
            return true;
        }
    }

    public function errorCode(): ?string
    {
        return $this->decorated->errorCode();
    }

    public function errorInfo(): array
    {
        return $this->decorated->errorInfo();
    }
}
