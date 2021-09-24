<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class ConnectionDataFlowsInstaller implements FixtureInstaller
{
    private Connection $dbConnection;

    private array $sourceConnections = [];

    private array $destinationConnections = [];

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function install(): void
    {
        $this->loadAuditableConnections();
        $now = new \DateTimeImmutable('now');

        $this->installProductsCreatedFlows($now);
        $this->installProductsUpdatedFlows($now);
        $this->installProductsSentFlows($now);
    }

    private function loadAuditableConnections()
    {
        $selectConnections = <<<SQL
SELECT code
FROM akeneo_connectivity_connection
WHERE auditable = 1 and flow_type = :flow_type;
SQL;
        $this->sourceConnections = $this->dbConnection->executeQuery(
            $selectConnections,
            ['flow_type' => FlowType::DATA_SOURCE]
        )->fetchAll(FetchMode::COLUMN);

        $this->destinationConnections = $this->dbConnection->executeQuery(
            $selectConnections,
            ['flow_type' => FlowType::DATA_DESTINATION]
        )->fetchAll(FetchMode::COLUMN);
    }

    private function dataFlowsDateTimes(\DateTimeImmutable $now): \Iterator
    {
        // 8 days before now
        for ($day = 1; $day <= 8 ; $day++) {
            yield $now
                ->setTime(rand(1, 23), rand(0, 59), rand(0, 59))
                ->modify("-$day days");
        }

        yield $now;

        // And 20 days after now
        for ($day = 1; $day <= 20 ; $day++) {
            yield $now
                ->setTime(rand(1, 23), rand(0, 59), rand(0, 59))
                ->modify("+$day days");
        }
    }

    private function installProductsCreatedFlows(\DateTimeImmutable $now): void
    {
        foreach ($this->dataFlowsDateTimes($now) as $date) {
            $total = 0;
            foreach ($this->sourceConnections as $sourceConnectionCode) {
                $count = rand(0, 30);
                $this->createConnectionAuditProduct($sourceConnectionCode, $date, $count, EventTypes::PRODUCT_CREATED);
                $total += $count;
            }

            $this->createConnectionAuditProduct('<all>', $date, $total, EventTypes::PRODUCT_CREATED);
        }
    }

    private function installProductsUpdatedFlows(\DateTimeImmutable $now): void
    {
        foreach ($this->dataFlowsDateTimes($now) as $date) {
            $total = 0;
            foreach ($this->sourceConnections as $sourceConnectionCode) {
                $count = rand(0, 100);
                $this->createConnectionAuditProduct($sourceConnectionCode, $date, $count, EventTypes::PRODUCT_UPDATED);
                $total += $count;
            }

            $this->createConnectionAuditProduct('<all>', $date, $total, EventTypes::PRODUCT_UPDATED);
        }
    }

    private function installProductsSentFlows(\DateTimeImmutable $now): void
    {
        foreach ($this->dataFlowsDateTimes($now) as $date) {
            $total = 0;
            foreach ($this->destinationConnections as $connectionCode) {
                $count = rand(0, 200);
                $this->createConnectionAuditProduct($connectionCode, $date, $count, EventTypes::PRODUCT_READ);
                $total += $count;
            }

            $this->createConnectionAuditProduct('<all>', $date, $total, EventTypes::PRODUCT_READ);
        }
    }

    private function createConnectionAuditProduct(string $code, \DateTimeImmutable $date, int $count, string $type): void
    {
        $query = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_product (connection_code, event_datetime, event_count, event_type, updated)
VALUES (:code, :date, :count, :type, :date)
SQL;
        $this->dbConnection->executeQuery($query, [
            'code' => $code,
            'date' => $date->format('Y-m-d H:i:s'),
            'count' => $count,
            'type' => $type,
        ]);
    }
}
