<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\WebhookLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectWebhookSecretQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class DbalSelectWebhookSecretQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var WebhookLoader */
    private $webhookLoader;

    /** @var SelectWebhookSecretQuery */
    private $selectWebhookSecret;

    public function test_to_select_a_webhook_secret(): void
    {
        $magentoConnection = $this->connectionLoader->createConnection(
            'magento',
            'Magento',
            FlowType::DATA_DESTINATION,
            true
        );
        $this->webhookLoader->initWebhook($magentoConnection->code());

        $secret = $this->selectWebhookSecret->execute($magentoConnection->code());
        Assert::assertEquals('secret', $secret);
    }

    public function test_to_select_webhook_secret_that_is_null(): void
    {
        $magentoConnection = $this->connectionLoader->createConnection(
            'magento',
            'Magento',
            FlowType::DATA_DESTINATION,
            true
        );

        $secret = $this->selectWebhookSecret->execute($magentoConnection->code());
        Assert::assertNull($secret);
    }

    public function test_to_select_a_secret_but_the_connection_does_not_exist(): void
    {
        $secret = $this->selectWebhookSecret->execute('shopify');
        Assert::assertNull($secret);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->selectWebhookSecret = $this->get('akeneo_connectivity.connection.persistence.query.select_webhook_secret');
    }
}
