<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAuditDataCommand extends Command
{
    /** @var Connection */
    private $dbalConnection;

    protected static $defaultName = 'akeneo:connectivity-audit:generate-data';

    public function __construct(Connection $dbalConnection)
    {
        parent::__construct();
        $this->dbalConnection = $dbalConnection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $connections = ['magento', 'glaucus', 'nyeux'];
        $dates = [];
        for ($i = 0; $i !== 8; $i++) {
            $date = new \DateTime('now');
            $date->modify("-$i days");
            $dates[] = $date;
        }
        $insertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit (connection_code, event_date, event_count, event_type)
VALUES (:code, :date, :count, :type)
SQL;

        foreach ($dates as $date) {
            foreach (['product_created', 'product_updated'] as $eventType) {
                $total = 0;
                foreach ($connections as $connection) {
                    $count = rand(0, 20000);
                    $stmt = $this->dbalConnection->prepare($insertQuery);
                    $stmt->execute([
                        'code' => $connection,
                        'date' => $date->format('Y-m-d'),
                        'count' => 0,
                        'type' => $eventType,
                    ]);
                    $total += $count;
                }
                $stmt = $this->dbalConnection->prepare($insertQuery);
                $stmt->execute([
                    'code' => '<all>',
                    'date' => $date->format('Y-m-d'),
                    'count' => 0,
                    'type' => $eventType,
                ]);
            }
        }
    }
}
