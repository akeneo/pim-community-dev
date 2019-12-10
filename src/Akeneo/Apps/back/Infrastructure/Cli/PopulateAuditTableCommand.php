<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Application\Query\FindAnAppHandler;
use Akeneo\Apps\Application\Query\FindAnAppQuery;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PopulateAuditTableCommand extends Command
{
    protected static $defaultName = 'akeneo:apps-audit:populate';

    /** @var Connection */
    private $dbalConnection;
    /** @var FetchAppsHandler */
    private $fetchAppsHandler;

    public function __construct(
        Connection $dbalConnection,
        FetchAppsHandler $fetchAppsHandler
    ) {
        parent::__construct();
        $this->dbalConnection = $dbalConnection;
        $this->fetchAppsHandler = $fetchAppsHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dates = ['2019-12-08', '2019-12-09', '2019-12-10', '2019-12-11', '2019-12-12', '2019-12-13', '2019-12-14', '2019-12-15', '2019-12-16', '2019-12-17', '2019-12-18'];
        $apps = $this->fetchAppsHandler->query();

        foreach ($apps as $app) {
            foreach ($dates as $date) {
                foreach (['product_created', 'product_updated'] as $eventType) {
                    $this->insertAuditData($app->code(), $date, rand(1, 10000), $eventType);
                }
            }
        }
    }

    private function insertAuditData($appCode, $eventDate, $eventCount, $eventType): void
    {
        $sqlQuery = <<<SQL
INSERT INTO akeneo_app_audit (app_code, event_date, event_count, event_type)
VALUES (:app_code, :event_date, :event_count, :event_type)
SQL;
        $this->dbalConnection->executeQuery(
            $sqlQuery,
            [
                'app_code' => $appCode,
                'event_date' => $eventDate,
                'event_count' => $eventCount,
                'event_type' => $eventType
            ],
            [
                'app_code' => \PDO::PARAM_STR,
                'event_date' => \PDO::PARAM_STR,
                'event_count' => \PDO::PARAM_INT,
                'event_type' => \PDO::PARAM_STR
            ]
        );
    }
}
