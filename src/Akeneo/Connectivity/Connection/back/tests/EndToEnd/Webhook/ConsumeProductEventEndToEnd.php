<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConsumeProductEventEndToEnd extends ApiTestCase
{
    private ProductInterface $referenceProduct;
    private Author $referenceAuthor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceProduct = $this->loadReferenceProduct();
        $this->referenceAuthor = Author::fromNameAndType('julia', Author::TYPE_UI);
        $connection = $this->loadConnection();

        $this->get('akeneo_connectivity.connection.fixtures.webhook_loader')->initWebhook($connection->code());
    }

    public function test_it_sends_a_product_created_webhook_event()
    {
        $container = [];
        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductCreated(
                    $this->referenceAuthor,
                    ['identifier' => $this->referenceProduct->getIdentifier()],
                    1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        Assert::assertCount(1, $container);

        /** @var Request $request */
        $request = $container[0]['request'];
        $requestContent = json_decode($request->getBody()->getContents(), true)['events'][0];
        $requestContent = $this->cleanRequestContent($requestContent);

        $this->assertEquals($this->expectedProductCreatedPayload(), $requestContent);
    }

    public function test_it_sends_a_product_updated_webhook_event()
    {
        $container = [];
        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductUpdated(
                    $this->referenceAuthor,
                    ['identifier' => $this->referenceProduct->getIdentifier()],
                    1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        Assert::assertCount(1, $container);

        /** @var Request $request */
        $request = $container[0]['request'];
        $requestContent = json_decode($request->getBody()->getContents(), true)['events'][0];
        $requestContent = $this->cleanRequestContent($requestContent);

        $this->assertEquals($this->expectedProductUpdatedPayload(), $requestContent);
    }

    public function test_it_sends_a_product_removed_webhook_event()
    {
        $container = [];
        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductRemoved(
                    $this->referenceAuthor,
                    [
                        'identifier' => $this->referenceProduct->getIdentifier(),
                        'category_codes' => $this->referenceProduct->getCategoryCodes(),
                    ],
                    1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);

        /** @var Request $request */
        $request = $container[0]['request'];
        $requestContent = json_decode($request->getBody()->getContents(), true)['events'][0];

        $this->assertEquals($this->expectedProductRemovedPayload(), $requestContent);
    }

    private function loadReferenceProduct(): ProductInterface
    {
        $this->get('akeneo_connectivity.connection.fixtures.enrichment.category')
            ->create(['code' => 'category']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'boolean_attribute', 'type' => 'pim_catalog_boolean']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'text_attribute', 'type' => 'pim_catalog_text']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'another_text_attribute', 'type' => 'pim_catalog_text']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.family')
            ->create(['code' => 'family', 'attributes' => ['boolean_attribute', 'text_attribute']]);

        return $this->get('akeneo_connectivity.connection.fixtures.enrichment.product')->create(
            'product',
            [
                'family' => 'family',
                'enabled' => true,
                'categories' => ['category'],
                'groups' => [],
                'values' => [
                    'another_text_attribute' => [
                        ['data' => 'text attribute', 'locale' => null, 'scope' => null],
                    ],
                ],
            ]
        );
    }

    private function loadConnection(): ConnectionWithCredentials
    {
        $connection = $this->get('akeneo_connectivity.connection.fixtures.connection_loader')
            ->createConnection(
                'ecommerce',
                'Ecommerce',
                FlowType::DATA_DESTINATION,
                false
            );
        $this->get('akeneo_connectivity.connection.fixtures.connection_loader')->update(
            $connection->code(),
            $connection->label(),
            $connection->flowType(),
            $connection->image(),
            $connection->userRoleId(),
            (string) $this->get('pim_user.repository.group')->findOneByIdentifier('IT support')->getId(),
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

    private function expectedProductCreatedPayload(): array
    {
        return [
            'action' => 'product.created',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_date' => '2020-12-04T16:02:47+01:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => $this->expectedData(),
        ];
    }

    private function expectedProductUpdatedPayload(): array
    {
        return [
            'action' => 'product.updated',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_date' => '2020-12-04T16:02:47+01:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => $this->expectedData(),
        ];
    }

    private function expectedProductRemovedPayload(): array
    {
        return [
            'action' => 'product.removed',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_date' => '2020-12-04T16:02:47+01:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => [
                'resource' => [
                    'identifier' => 'product',
                ],
            ],
        ];
    }

    private function expectedData(): array
    {
        return [
            'resource' => [
                'identifier' => 'product',
                'enabled' => true,
                'family' => 'family',
                'groups' => [],
                'parent' => null,
                'categories' => ['category'],
                'values' => [
                    'another_text_attribute' => [
                        [
                            'data' => 'text attribute',
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
