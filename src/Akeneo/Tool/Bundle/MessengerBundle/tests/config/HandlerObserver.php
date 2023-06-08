<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\config;

use Akeneo\Tool\Component\Messenger\CorrelationAwareInterface;
use Akeneo\Tool\Component\Messenger\NormalizableMessageInterface;
use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Doctrine\DBAL\Connection;

/**
 * Register the handler executions. As the handler are called in subprocess,
 * we can't store the executions only in memory. So we insert them in DB.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandlerObserver
{
    private const PIM_CONF_CODE = 'messenger.handler_observer';

    private array $executedHandlers = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function handlerWasExecuted(string $class, NormalizableMessageInterface $message): void
    {
        $this->executedHandlers[] = [
            'class' => $class,
            'message' => $message->normalize(),
            'correlation_id' => $message instanceof CorrelationAwareInterface ? $message->getCorrelationId() : null,
            'tenant_id' => $message instanceof TenantAwareInterface ? $message->getTenantId() : null,
        ];
        $this->saveInDb();
    }

    public function getHandlerNumberOfExecution(string $handlerClass): int
    {
        $this->loadFromDb();

        return \count(\array_filter(
            $this->executedHandlers,
            static fn ($execution): bool => $execution['class'] === $handlerClass
        ));
    }

    public function messageIsHandledByHandler(string $correlationId, string $handlerClass): bool
    {
        $this->loadFromDb();

        foreach ($this->executedHandlers as $execution) {
            $messageCorrelationId = $execution['correlation_id'] ?? null;
            if ($execution['class'] === $handlerClass && $correlationId === $messageCorrelationId) {
                return true;
            }
        }

        return false;
    }

    public function getTotalNumberOfExecution(): int
    {
        $this->loadFromDb();

        return \count($this->executedHandlers);
    }

    public function reset(): void
    {
        $query = <<<SQL
DELETE FROM pim_configuration WHERE code = :code
SQL;
        $this->connection->executeQuery($query, ['code' => self::PIM_CONF_CODE]);
    }

    private function saveInDb(): void
    {
        $query = <<<SQL
INSERT INTO pim_configuration (`code`, `values`) VALUES (:code, :values)
ON DUPLICATE KEY UPDATE `values` = :values
SQL;
        $this->connection->executeQuery($query, [
            'code' => self::PIM_CONF_CODE,
            'values' => \json_encode($this->executedHandlers),
        ]);
    }

    private function loadFromDb(): void
    {
        $query = <<<SQL
SELECT `values` FROM pim_configuration WHERE code = :code
SQL;
        $values = $this->connection->executeQuery($query, [
            'code' => self::PIM_CONF_CODE,
        ])->fetchOne();

        if ($values) {
            $this->executedHandlers = \json_decode($values, true);
        }
    }
}
