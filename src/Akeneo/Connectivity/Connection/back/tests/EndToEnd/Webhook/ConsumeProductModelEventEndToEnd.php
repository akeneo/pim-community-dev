<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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

class ConsumeProductModelEventEndToEnd extends ApiTestCase
{
    private ProductModelInterface $referenceProductModel;
    private Author $referenceAuthor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceProductModel = $this->loadReferenceProductModel();
        $this->referenceAuthor = Author::fromNameAndType('julia', Author::TYPE_UI);
        $connection = $this->loadConnection();

        $this->get('akeneo_connectivity.connection.fixtures.webhook_loader')->initWebhook($connection->code());
    }

    public function test_it_sends_a_product_model_created_webhook_event()
    {
        $container = [];
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelCreated(
                    $this->referenceAuthor,
                    ['code' => $this->referenceProductModel->getCode(),],
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
        $requestContent = $this->cleanRequestContent($requestContent);

        $this->assertEquals($this->expectedProductModelCreatedPayload(), $requestContent);
    }

    public function test_it_sends_a_product_model_updated_webhook_event()
    {
        $container = [];
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelUpdated(
                    $this->referenceAuthor,
                    ['code' => $this->referenceProductModel->getCode()],
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
        $requestContent = $this->cleanRequestContent($requestContent);

        $this->assertEquals($this->expectedProductModelUpdatedPayload(), $requestContent);
    }

    public function test_it_sends_a_product_model_removed_webhook_event()
    {
        $container = [];
        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelRemoved(
                    $this->referenceAuthor,
                    [
                        'code' => $this->referenceProductModel->getCode(),
                        'category_codes' => $this->referenceProductModel->getCategoryCodes(),
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

        /** @var $request */
        $request = $container[0]['request'];
        $requestContent = json_decode($request->getBody()->getContents(), true)['events'][0];

        $this->assertEquals($this->expectedProductModelRemovedPayload(), $requestContent);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @throws \Exception
     */
    private function loadReferenceProductModel(): ProductModelInterface
    {
        $this->get('akeneo_connectivity.connection.fixtures.enrichment.category')
            ->create(['code' => 'category']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'variant_attribute', 'type' => 'pim_catalog_boolean']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'text_attribute', 'type' => 'pim_catalog_text']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.attribute')
            ->create(['code' => 'another_text_attribute', 'type' => 'pim_catalog_text']);
        $this->get('akeneo_connectivity.connection.fixtures.structure.family')
            ->create(['code' => 'family', 'attributes' => ['variant_attribute', 'text_attribute', 'another_text_attribute']]);
        $familyVariant = $this->get('akeneo_connectivity.connection.fixtures.enrichment.family_variant')
            ->create([
                    'code' => 'family_variant',
                    'family' => 'family',
                    'variant_attribute_sets' => [
                        [
                            'axes' => ['variant_attribute'],
                            'attributes' => [],
                            'level' => 1,
                        ],
                    ],
                ]
            );

        return $this->get('akeneo_connectivity.connection.fixtures.enrichment.product_model')
            ->create(
                [
                    'code' => 'product_model',
                    'categories' => ['category'],
                    'family_variant' => $familyVariant->getCode(),
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

    private function expectedProductModelUpdatedPayload(): array
    {
        return [
            'action' => 'product_model.updated',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_datetime' => '2020-12-04T16:02:47+01:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => $this->expectedData(),
        ];
    }

    private function expectedProductModelCreatedPayload(): array
    {
        return [
            'action' => 'product_model.created',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_datetime' => '2020-12-04T16:02:47+01:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => $this->expectedData(),
        ];
    }

    private function expectedProductModelRemovedPayload(): array
    {
        return [
            'action' => 'product_model.removed',
            'event_id' => '0d931d13-8eae-4f4a-bf37-33d3a932b8c9',
            'event_datetime' => '2020-12-04T16:02:47+01:00',
            'author' => 'julia',
            'author_type' => 'ui',
            'pim_source' => 'http://localhost:8080',
            'data' => [
                'resource' => [
                    'code' => 'product_model',
                ],
            ],
        ];
    }

    private function expectedData(): array
    {
        return [
            'resource' => [
                'code' => 'product_model',
                'family' => 'family',
                'family_variant' => 'family_variant',
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
}
