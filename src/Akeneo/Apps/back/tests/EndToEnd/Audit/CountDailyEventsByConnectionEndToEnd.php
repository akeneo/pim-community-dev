<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd\Audit;

use Akeneo\Apps\back\tests\EndToEnd\WebTestCase;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionEndToEnd extends WebTestCase
{
    public function test_it_finds_connections_event_by_created_product()
    {
        $this->get('akeneo_app.fixtures.app_loader')->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE);
        $this->get('akeneo_app.fixtures.app_loader')->createConnection('erp', 'ERP', FlowType::DATA_SOURCE);
        $this->loadAuditData();
        $this->createAdminUser();
        $this->authenticate('admin', 'admin');

        $this->client->request('GET', '/rest/apps/audit/source-apps-event', ['event_type' => 'product_created']);
        $response = $this->client->getResponse();

        Assert::assertTrue($response->isOk());
        Assert::assertJsonStringNotEqualsJsonFile(
            realpath(__DIR__.'/../Resources/json_response/count_daily_events_by_connection.json'),
            $response->getContent()
        );
    }

    private function loadAuditData(): void
    {
        $eventDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $auditLoader = $this->get('akeneo_app.fixtures.audit_loader');
        // today
        $auditLoader->insertData('franklin', $eventDate, 11, 'product_created');
        $auditLoader->insertData('erp', $eventDate, 28, 'product_updated');
        $auditLoader->insertData('erp', $eventDate, 37, 'product_created');
        // yesterday
        $auditLoader->insertData('franklin', $eventDate->modify('-1 day'), 5, 'product_created');
        $auditLoader->insertData('franklin', $eventDate, 132, 'product_updated');
        // 2 days ago
        $auditLoader->insertData('franklin', $eventDate->modify('-1 day'), 10, 'product_created');
        $auditLoader->insertData('franklin', $eventDate, 7, 'product_created');
        // 10 days ago
        $auditLoader->insertData('franklin', $eventDate->modify('-7 day'), 15, 'product_created');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
