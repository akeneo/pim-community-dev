<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\MessageHandler\BusinessEventHandler;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\Tests\EndToEnd\GuzzleJsonHistoryContainer;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Doctrine\DBAL\Connection as DbalConnection;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConsumeProductEventEndToEnd extends ApiTestCase
{
    private ProductInterface $tshirtProduct;
    private ProductInterface $pantProduct;
    private Author $referenceAuthor;
    private DbalConnection $dbalConnection;
    private ProductLoader $productLoader;
    private GuzzleJsonHistoryContainer $historyContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_connectivity.connection.fixtures.enrichment.category')
            ->create(['code' => 'sea']);
        $this->get('akeneo_connectivity.connection.fixtures.enrichment.category')
            ->create(['code' => 'fiesta']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'boolean_attribute', 'type' => 'pim_catalog_boolean']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'text_attribute', 'type' => 'pim_catalog_text']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'another_text_attribute', 'type' => 'pim_catalog_text']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.family')
            ->create(['code' => 'tshirt', 'attributes' => ['boolean_attribute', 'text_attribute']]);
        $this->get('akeneo_connectivity.connection.fixtures.structure.family')
            ->create(['code' => 'pant', 'attributes' => ['boolean_attribute', 'text_attribute']]);

        $this->referenceAuthor = Author::fromNameAndType('julia', Author::TYPE_UI);
        $this->dbalConnection = static::getContainer()->get('database_connection');
        $this->productLoader = static::getContainer()->get(
            'akeneo_connectivity.connection.fixtures.enrichment.product'
        );
        $this->historyContainer = $this->get(GuzzleJsonHistoryContainer::class);

        $this->tshirtProduct = $this->productLoader->create('blue-t-shirt', [
            new SetFamily('tshirt'),
            new SetEnabled(true),
            new SetCategories(['sea']),
            new SetTextValue('another_text_attribute', null, null, 'text attribute')
        ]);
        $this->pantProduct = $this->productLoader->create('red-pant', [
            new SetFamily('pant'),
            new SetEnabled(true),
            new SetCategories(['fiesta']),
            new SetTextValue('another_text_attribute', null, null, 'text attribute')
        ]);

        $connection = $this->loadConnection();

        $this->get('akeneo_connectivity.connection.fixtures.webhook_loader')->initWebhook($connection->code());
    }

    public function test_it_sends_a_product_created_webhook_event(): void
    {
        $message = new BulkEvent(
            [
                new ProductCreated(
                    $this->referenceAuthor,
                    [
                        'identifier' => $this->tshirtProduct->getIdentifier(),
                        'uuid' => $this->tshirtProduct->getUuid(),
                    ],
                    1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
                new ProductCreated(
                    $this->referenceAuthor,
                    [
                        'identifier' => $this->pantProduct->getIdentifier(),
                        'uuid' => $this->pantProduct->getUuid(),
                    ],
                    1607094167,
                    '0d932313-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        Assert::assertCount(1, $this->historyContainer);

        /** @var Request $requestObject */
        $request = $this->historyContainer[0]['request'];
        $requestObject = Message::parseRequest($request);
        $requestContent = \json_decode($requestObject->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)['events'][0];
        $requestContent = $this->cleanRequestContent($requestContent);

        Assert::assertEquals(2, (int)$this->getEventCount('ecommerce'));
        $this->assertEquals($this->expectedProductCreatedPayload($this->tshirtProduct), $requestContent);
    }

    public function test_it_sends_a_product_updated_webhook_event(): void
    {
        $message = new BulkEvent(
            [
                new ProductUpdated(
                    $this->referenceAuthor,
                    [
                        'identifier' => $this->tshirtProduct->getIdentifier(),
                        'uuid' => $this->tshirtProduct->getUuid(),
                    ],
                    1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        Assert::assertCount(1, $this->historyContainer);

        $request = $this->historyContainer[0]['request'];
        $requestObject = Message::parseRequest($request);
        $requestContent = \json_decode($requestObject->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)['events'][0];
        $requestContent = $this->cleanRequestContent($requestContent);

        Assert::assertEquals(1, (int)$this->getEventCount('ecommerce'));
        $this->assertEquals($this->expectedProductUpdatedPayload($this->tshirtProduct), $requestContent);
    }

    public function test_it_sends_a_product_removed_webhook_event(): void
    {
        $message = new BulkEvent(
            [
                new ProductRemoved(
                    $this->referenceAuthor,
                    [
                        'identifier' => $this->tshirtProduct->getIdentifier(),
                        'uuid' => $this->tshirtProduct->getUuid(),
                        'category_codes' => $this->tshirtProduct->getCategoryCodes(),
                    ],
                    1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $this->historyContainer);

        $request = Message::parseRequest($this->historyContainer[0]['request']);
        $requestContent = \json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)['events'][0];

        Assert::assertEquals(1, (int)$this->getEventCount('ecommerce'));
        $this->assertEquals($this->expectedProductRemovedPayload($this->tshirtProduct), $requestContent);
    }

    private function loadConnection(): ConnectionWithCredentials
    {
        $connection = $this->get('akeneo_connectivity.connection.fixtures.connection_loader')
            ->createConnection(
                'ecommerce',
                'Ecommerce',
                FlowType::DATA_DESTINATION,
                true
            );
        $this->get('akeneo_connectivity.connection.fixtures.connection_loader')->update(
            $connection->code(),
            $connection->label(),
            $connection->flowType(),
            $connection->image(),
            $connection->userRoleId(),
            (string)$this->get('pim_user.repository.group')->findOneByIdentifier('IT support')->getId(),
            $connection->auditable(),
        );

        return $connection;
    }

    private function cleanRequestContent(array $requestContent): array
    {
        NormalizedProductCleaner::clean($requestContent['data']['resource']);

        // We remove metadata since it only exists in EE
        if (isset($requestContent['data']['resource']['metadata'])) {
            unset($requestContent['data']['resource']['metadata']);
        }

        return $requestContent;
    }

    private function expectedProductCreatedPayload(ProductInterface $productCreated): array
    {
        return [
            'action' => 'product.created',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_datetime' => '2020-12-04T15:02:47+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => $this->expectedData($productCreated),
        ];
    }

    private function expectedProductUpdatedPayload(ProductInterface $productUpdated): array
    {
        return [
            'action' => 'product.updated',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_datetime' => '2020-12-04T15:02:47+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => $this->expectedData($productUpdated),
        ];
    }

    private function expectedProductRemovedPayload(ProductInterface $product): array
    {
        return [
            'action' => 'product.removed',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_datetime' => '2020-12-04T15:02:47+00:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => [
                'resource' => [
                    'identifier' => $product->getIdentifier(),
                    'uuid' => $product->getUuid(),
                ],
            ],
        ];
    }

    private function expectedData(ProductInterface $product): array
    {
        return [
            'resource' => [
                'uuid' => $product->getUuid()->toString(),
                'identifier' => $product->getIdentifier(),
                'enabled' => true,
                'family' => $product->getFamily()->getCode(),
                'groups' => [],
                'parent' => null,
                'categories' => $product->getCategoryCodes(),
                'values' => [
                    'another_text_attribute' => [
                        [
                            'data' => 'text attribute',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                    'sku' => [
                        [
                            'data' => $product->getIdentifier(),
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                ],
                'created' => 'this is a date formatted to ISO-8601',
                'updated' => 'this is a date formatted to ISO-8601',
                'associations' => [
                    'PACK' => [
                        'groups' => [],
                        'product_models' => [],
                        'products' => [],
                    ],
                    'SUBSTITUTION' => [
                        'groups' => [],
                        'product_models' => [],
                        'products' => [],
                    ],
                    'UPSELL' => [
                        'groups' => [],
                        'product_models' => [],
                        'products' => [],
                    ],
                    'X_SELL' => [
                        'groups' => [],
                        'product_models' => [],
                        'products' => [],
                    ],
                ],
                'quantified_associations' => [],
            ],
        ];
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
