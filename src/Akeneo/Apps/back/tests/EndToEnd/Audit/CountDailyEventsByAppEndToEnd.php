<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd\Audit;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByAppEndToEnd extends TestCase
{
    public function test_it_finds_apps_event_by_created_product()
    {
        $this->createApp('franklin', FlowType::DATA_SOURCE);
        $this->createApp('as400', FlowType::DATA_SOURCE);

        $dates = ['2019-12-08', '2019-12-09', '2019-12-10', '2019-12-11'];
        foreach (['franklin', 'as400'] as $appCode) {
            $count = 0;
            foreach ($dates as $date) {
                foreach (['product_created', 'product_updated'] as $eventType) {
                    $this->insertAuditData($appCode, $date, $count++, $eventType);
                }
            }
        }
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertAuditData($appCode, $eventDate, $eventCount, $eventType): void
    {
        $sqlQuery = <<<SQL
INSERT INTO akeneo_app_audit (app_code, event_date, event_count, event_type)
VALUES (:app_code, :event_date, :event_count, :event_type)
SQL;
        $this->get('database_connection')->executeQuery(
            $sqlQuery,
            [
                'app_code' => $appCode,
                'event_date' => $eventDate,
                'event_count' => $eventCount,
                'event_type' => $eventType
            ]
        );
    }

    private function createApp($appCode, $flowType)
    {
        $command = new CreateAppCommand($appCode, $appCode, $flowType);
        $this->get('akeneo_app.application.handler.create_app')->handle($command);
    }
}
