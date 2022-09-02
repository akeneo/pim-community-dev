<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask;

use Doctrine\DBAL\Connection;

trait OneTimeTaskCommandTrait
{
    private Connection $dbConnection;

    private function startTask(string $taskCode): void
    {
        $query = <<<SQL
INSERT IGNORE INTO pim_one_time_task (code, status, start_time) 
VALUES (:code, 'started', NOW());
SQL;

        $this->dbConnection->executeQuery($query, ['code' => $taskCode]);
    }

    private function finishTask(string $taskCode): void
    {
        $query = <<<SQL
UPDATE pim_one_time_task 
SET status = 'done', end_time = NOW()
WHERE code = :code;
SQL;

        $this->dbConnection->executeQuery($query, ['code' => $taskCode]);
    }

    private function deleteTask(string $taskCode): void
    {
        $query = <<<SQL
DELETE FROM pim_one_time_task WHERE code = :code;
SQL;
        $this->dbConnection->executeQuery($query, ['code' => $taskCode]);
    }

    private function taskCanBeStarted(string $taskCode): bool
    {
        $query = <<<SQL
SELECT 1 FROM pim_one_time_task WHERE code = :code;
SQL;

        return !(bool)$this->dbConnection->executeQuery($query, ['code' => $taskCode])->fetchOne();
    }
}
