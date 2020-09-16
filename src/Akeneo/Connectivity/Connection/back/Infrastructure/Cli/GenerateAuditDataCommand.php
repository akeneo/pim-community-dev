<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAuditDataCommand extends Command
{
    /** @var Connection */
    private $dbalConnection;

    /** @var string[] */
    private $sourceConnections;

    /** @var string[] */
    private $destinationConnections;

    protected static $defaultName = 'akeneo:connectivity-audit:generate-data';

    public function __construct(Connection $dbalConnection)
    {
        parent::__construct();

        $this->dbalConnection = $dbalConnection;
        $this->sourceConnections = [];
        $this->destinationConnections = [];
    }

    /**
     *
    Akeneo\Connectivity\Connection\Infrastructure\Cli\GenerateAuditDataCommand:
    class: Akeneo\Connectivity\Connection\Infrastructure\Cli\GenerateAuditDataCommand
    arguments:
    - '@database_connection'
    tags:
    - { name: 'console.command' }
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeConnections();

        $nowDatetime = new \DateTime("now");
        $now = $nowDatetime->format('Y-m-d H:i:s');

        $datetimes = $this->getDatetimeForEveryHourSince9Days();

        foreach ($datetimes as $date) {
            $this->insertSourceAudit($now, $date);
            $this->insertDestinationAudit($now, $date);
        }

        return 1;
    }

    private function getDatetimeForEveryHourSince9Days(): array
    {
        $datetimes = [];
        for ($i = 0; $i !== 9; $i++) {
            for ($j = 0; $j !== 24; $j++) {
                $date = new \DateTime('now');
                $date->setTime((int) $date->format('H'), 0, 0);
                $date->modify("-$i days");
                $date->modify("-$j hours");
                $datetimes[] = $date->format('Y-m-d H:i:s');
            }
        }

        return $datetimes;
    }

    private function initializeConnections()
    {
        $selectConnections = <<<SQL
SELECT code
FROM akeneo_connectivity_connection
WHERE auditable = 1 and flow_type = :flow_type;
SQL;
        $this->sourceConnections = $this->dbalConnection->executeQuery(
            $selectConnections,
            ['flow_type' => FlowType::DATA_SOURCE]
        )->fetchAll(FetchMode::COLUMN);
        $this->destinationConnections = $this->dbalConnection->executeQuery(
            $selectConnections,
            ['flow_type' => FlowType::DATA_DESTINATION]
        )->fetchAll(FetchMode::COLUMN);
    }

    private function insertDestinationAudit(string $now, string $date): void
    {
        $total = 0;
        foreach ($this->destinationConnections as $destinationConnection) {
            $count = rand(0, 2000);
            $stmt = $this->dbalConnection->prepare($this->getInsertSql());
            $stmt->execute([
                'code' => $destinationConnection,
                'date' => $date,
                'count' => $count,
                'type' => EventTypes::PRODUCT_READ,
                'now' => $now
            ]);
            $total += $count;
        }
        $stmt = $this->dbalConnection->prepare($this->getInsertSql());
        $stmt->execute([
            'code' => '<all>',
            'date' => $date,
            'count' => $total,
            'type' => EventTypes::PRODUCT_READ,
            'now' => $now
        ]);
    }

    private function insertSourceAudit(string $now, string $date): void
    {
        foreach (['product_created', 'product_updated'] as $eventType) {
            $total = 0;
            foreach ($this->sourceConnections as $sourceConnections) {
                $count = rand(0, 2000);
                $stmt = $this->dbalConnection->prepare($this->getInsertSql());
                $stmt->execute([
                    'code' => $sourceConnections,
                    'date' => $date,
                    'count' => $count,
                    'type' => $eventType,
                    'now' => $now
                ]);
                $total += $count;
            }
            $stmt = $this->dbalConnection->prepare($this->getInsertSql());
            $stmt->execute([
                'code' => '<all>',
                'date' => $date,
                'count' => $total,
                'type' => $eventType,
                'now' => $now
            ]);
        }
    }

    private function getInsertSql(): string
    {
        return <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_product (connection_code, event_datetime, event_count, event_type, updated)
VALUES (:code, :date, :count, :type, :now)
SQL;
    }
}
