<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SaveWebhookSecretQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class DbalSaveWebhookSecretQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var DbalConnection */
    private $dbalConnection;

    /** @var SaveWebhookSecretQuery */
    private $saveSecret;

    public function test_it_saves_a_webhook_secret(): void
    {
        $magentoConnection = $this->connectionLoader->createConnection(
            'magento',
            'Magento',
            FlowType::DATA_DESTINATION,
            true
        );

        $isUpdated = $this->saveSecret->execute($magentoConnection->code(), 'secret_1234');
        Assert::assertTrue($isUpdated);

        $fetchedSecret = $this->dbalConnection
            ->executeQuery(
                'SELECT webhook_secret FROM akeneo_connectivity_connection WHERE code = :code',
                ['code' => $magentoConnection->code()]
            )
            ->fetch();
        Assert::assertArrayHasKey('webhook_secret', $fetchedSecret);
        Assert::assertCount(1, $fetchedSecret);
        Assert::assertEquals('secret_1234', $fetchedSecret['webhook_secret']);
    }

    public function test_it_does_not_save_a_secret_on_a_connection_that_does_not_exist(): void
    {
        $isUpdated = $this->saveSecret->execute('shopify', 'secret_1234');
        Assert::assertFalse($isUpdated);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->saveSecret = $this->get('akeneo_connectivity.connection.persistence.query.save_webhook_secret');
    }
}
