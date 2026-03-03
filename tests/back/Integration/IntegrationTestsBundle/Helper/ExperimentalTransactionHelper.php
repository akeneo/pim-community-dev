<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Helper;

use Akeneo\Test\IntegrationTestsBundle\Loader\DatabaseSchemaHandler;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ExperimentalTransactionHelper
{
    private bool $active = false;

    public function __construct(
        private ManagerRegistry $doctrine,
        private DatabaseSchemaHandler $databaseSchemaHandler,
        private bool $enabled,
    ) {
    }

    public function disable(): void
    {
        $this->enabled = false;

        /** @var Connection $connection */
        foreach ($this->doctrine->getConnections() as $connection) {
            if ($connection->isTransactionActive()) {
                $connection->commit();
            }
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function beginTransactions(): void
    {
        if (!$this->enabled) {
            return;
        }

        /** @var Connection $connection */
        foreach ($this->doctrine->getConnections() as $connection) {
            $connection->setNestTransactionsWithSavepoints(true);
            $connection->beginTransaction();
        }

        $this->active = true;
    }

    public function abortTransactions(): void
    {
        if (!$this->enabled || !$this->active) {
            return;
        }

        /** @var Connection $connection */
        foreach ($this->doctrine->getConnections() as $connection) {
            $connection->rollBack();
        }

        $this->active = false;
    }

    public function closeTransactions(): void
    {
        if (!$this->enabled) {
            return;
        }

        if ($this->active) {
            /** @var Connection $connection */
            foreach ($this->doctrine->getConnections() as $connection) {
                $connection->rollBack();
            }

            $this->active = false;
        } else {
            $this->databaseSchemaHandler->reset();
        }
    }
}
