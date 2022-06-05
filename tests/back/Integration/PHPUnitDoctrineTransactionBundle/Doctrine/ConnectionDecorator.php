<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\ParameterType;

class ConnectionDecorator implements Driver\Connection
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
            if ($e->getMessage() === 'There is already an active transaction') {
                return true;
            }

            throw $e;
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
            // It happens that the opened transaction was automatically commited by mysql
            // For example, "TRUNCATE TABLE x" will commit the transaction.
            // see https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html
            // When it happens, it's normal we can't rollback anymore so we can ignore the error.
            if ($e->getMessage() === 'There is no active transaction') {
                return true;
            }

            throw $e;
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
