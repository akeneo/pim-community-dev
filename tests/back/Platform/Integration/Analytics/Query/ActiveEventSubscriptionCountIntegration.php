<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Analytics\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Analytics\ActiveEventSubscriptionCountQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActiveEventSubscriptionCountIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private ActiveEventSubscriptionCountQuery $activeEventSubscriptionCount;

    /** @var DbalConnection */
    private $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->activeEventSubscriptionCount = $this->get('pim_analytics.query.active_event_subscription_count');
        $this->dbalConnection = $this->get('database_connection');
    }

    public function test_it_fetches_active_event_subscription_count()
    {
        $this->createConnectionWithWebhookData(
            'erp',
            'ERP',
            FlowType::DATA_SOURCE,
            true,
            'secret_erp',
            'http://localhost/webhook_erp',
            true
        );

        $this->createConnectionWithWebhookData(
            'csv',
            'CSV',
            FlowType::DATA_SOURCE,
            true,
            'secret_csv',
            'http://localhost/webhook_csv',
            false
        );

        $this->createConnectionWithWebhookData(
            'magento',
            'MAGENTO',
            FlowType::DATA_DESTINATION,
            true,
            'secret_magento',
            'http://localhost/webhook_magento',
            true
        );

        $this->createConnectionWithWebhookData(
            'print',
            'PRINT',
            FlowType::DATA_DESTINATION,
            true,
            'secret_print',
            'http://localhost/webhook_print',
            false
        );

        $this->createConnectionWithWebhookData(
            'translations',
            'TRANSLATIONS',
            FlowType::OTHER,
            true,
            'secret_translations',
            'http://localhost/webhook_translations',
            true
        );

        $result = $this->activeEventSubscriptionCount->fetch();
        $expectedResult = 3;

        Assert::assertEquals($expectedResult, $result);

    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createConnectionWithWebhookData(
        string $code,
        string $label,
        string $flowType,
        bool $auditable,
        string $secret,
        ?string $url,
        bool $enabled
    ): void {

        $this->connectionLoader->createConnection($code, $label, $flowType, $auditable);

        $this->dbalConnection->update(
            'akeneo_connectivity_connection',
            [
                'webhook_url' => $url,
                'webhook_secret' => $secret,
                'webhook_enabled' => (int)$enabled,
            ],
            ['code' => $code]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
