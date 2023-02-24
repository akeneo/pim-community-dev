<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\config;

use Akeneo\Tool\Component\Messenger\SerializableMessageInterface;
use Doctrine\DBAL\Connection;

/**
 * Register the handler executions. As the handler are called in subprocess, we can't store the executions
 * only in memory. So we insert them in DB.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandlerObserver
{
    private array $executedHandlers = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function handlerWasExecuted(string $class, SerializableMessageInterface $message): void
    {
        $this->executedHandlers[] = [
            'class' => $class,
            'message' => $message->normalize(),
        ];
        $this->saveInDb();
    }

    public function getHandlerNumberOfExecution($class): int
    {
        $this->loadFromDb();

        return \count(\array_filter(
            $this->executedHandlers,
            static fn ($execution): bool  => $execution['class'] === $class
        ));
    }

    public function getTotalNumberOfExecution(): int
    {
        $this->loadFromDb();

        return \count($this->executedHandlers);
    }

    private function saveInDb(): void
    {
        $query = <<<SQL
INSERT INTO pim_configuration (`code`, `values`) VALUES (:code, :values)
ON DUPLICATE KEY UPDATE `values` = :values
SQL;
        $this->connection->executeQuery($query, [
            'code' => 'messenger.handler_observer',
            'values' => \json_encode($this->executedHandlers),
        ]);
    }

    private function loadFromDb(): void
    {
        $query = <<<SQL
SELECT `values` FROM pim_configuration WHERE code = :code
SQL;
        $values = $this->connection->executeQuery($query, [
            'code' => 'messenger.handler_observer',
        ])->fetchOne();

        if ($values) {
            $this->executedHandlers = \json_decode($values, true);
        }
    }
}
