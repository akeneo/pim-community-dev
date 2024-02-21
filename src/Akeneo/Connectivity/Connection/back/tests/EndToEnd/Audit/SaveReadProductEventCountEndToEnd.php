<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SaveReadProductEventCountEndToEnd extends ApiTestCase
{
    private Connection $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
    }

    public function test_it_save_read_product_event_count(): void
    {
        $this->createProduct('product1');
        $this->createProduct('product2');
        $this->createProduct('product3');

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $apiConnection = $this->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION);

        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnection->clientId(),
            $apiConnection->secret(),
            $apiConnection->username(),
            $apiConnection->password()
        );

        $apiClient->request('GET', 'api/rest/v1/products');
        $apiClient->getResponse()->getContent();

        Assert::assertEquals(3, (int) $this->getEventCount('ecommerce'));
    }

    public function test_it_save_and_increment_read_product_event_count(): void
    {
        $this->createProduct('product1');
        $this->createProduct('product2');

        \sleep(1); // we have to wait for ES indexation

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION);
        $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);

        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $apiClient->request('GET', 'api/rest/v1/products/product1');
        $apiClient->getResponse()->getContent();

        Assert::assertEquals(1, (int) $this->getEventCount('ecommerce'));

        $apiClient->getHistory()->clear();
        $apiClient->request('GET', 'api/rest/v1/products/product2');
        $apiClient->getResponse()->getContent();

        Assert::assertEquals(2, (int) $this->getEventCount('ecommerce'));
        Assert::assertEquals(0, (int) $this->getEventCount('magento'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProduct(string $identifier) : void
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(UpsertProductCommand::createWithIdentifierSystemUser(
            productIdentifier: $identifier,
            userIntents: []
        ));
    }

    private function getEventCount(string $connectionCode)
    {
        $sql = <<<SQL
SELECT event_count
FROM akeneo_connectivity_connection_audit_product
WHERE connection_code = :connection_code
AND event_type = 'product_read'
SQL;

        return $this->dbalConnection->fetchOne($sql, [
            'connection_code' => $connectionCode,
        ]);
    }
}
