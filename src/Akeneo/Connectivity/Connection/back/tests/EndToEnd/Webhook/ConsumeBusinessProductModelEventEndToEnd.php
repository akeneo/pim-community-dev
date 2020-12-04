<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\FamilyVariantLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductModelLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\WebhookLoader;
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

class ConsumeBusinessProductModelEventEndToEnd extends ApiTestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var WebhookLoader */
    private $webhookLoader;

    /** @var ProductModelLoader */
    private $productModelLoader;

    /** @var FamilyVariantLoader */
    private $familyVariantLoader;

    /** @var FamilyLoader */
    private $familyLoader;

    /** @var AttributeLoader */
    private $attributeLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->productModelLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product_model');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->familyVariantLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.family_variant');
    }

    public function test_it_sends_a_product_model_created_webhook_event()
    {
        $author = Author::fromUser($this->createAdminUser());
        $connection = $this->connectionLoader->createConnection(
            'ecommerce',
            'Ecommerce',
            FlowType::DATA_DESTINATION,
            false,
        );

        $this->webhookLoader->initWebhook($connection->code());

        $productModel = $this->loadProductModel();

        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelCreated(
                    $author, ['code' => $productModel->getCode()], 1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);
        $this->assertRequestPayloadEquals($container[0]['request'], $this->expectedProductModelCreatedPayload());
    }

    public function test_it_sends_a_product_model_updated_webhook_event()
    {
        $author = Author::fromUser($this->createAdminUser());
        $connection = $this->connectionLoader->createConnection(
            'ecommerce',
            'Ecommerce',
            FlowType::DATA_DESTINATION,
            false,
        );

        $this->webhookLoader->initWebhook($connection->code());

        $productModel = $this->loadProductModel();

        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelUpdated(
                    $author,
                    ['code' => $productModel->getCode()],
                    1607094167,
                    '0d931d13-8eae-4f4a-bf37-33d3a932b8c9'
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);
        $this->assertRequestPayloadEquals($container[0]['request'], $this->expectedProductModelUpdatedPayload());
    }

    public function test_it_sends_a_product_model_removed_webhook_event()
    {
        $author = Author::fromUser($this->createAdminUser());
        $connection = $this->connectionLoader->createConnection(
            'ecommerce',
            'Ecommerce',
            FlowType::DATA_DESTINATION,
            false,
        );
        $this->webhookLoader->initWebhook($connection->code());

        $productModel = $this->loadProductModel();

        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelRemoved(
                    $author,
                    ['code' => $productModel->getCode(), 'category_codes' => $productModel->getCategoryCodes()],
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
        $requestContent = json_decode($request->getBody()->getContents(), true)[0];
        $this->assertEquals($this->expectedProductModelRemovedPayload(), $requestContent);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @throws \Exception
     */
    private function loadProductModel(): ProductModelInterface
    {
        $this->attributeLoader->create(
            [
                'code' => 'variant_attribute',
                'type' => 'pim_catalog_boolean',
            ]
        );

        $this->attributeLoader->create(
            [
                'code' => 'text_attribute',
                'type' => 'pim_catalog_text',
            ]
        );

        $this->familyLoader->create(
            [
                'code' => 'family',
                'attributes' => ['variant_attribute', 'text_attribute'],
            ]
        );

        $familyVariant = $this->familyVariantLoader->create(
            [
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

        return $this->productModelLoader->create(
            [
                'code' => 'product_model',
                'family_variant' => $familyVariant->getCode(),
            ]
        );
    }

    // TODO : Remove method ?
    private function assertRequestPayloadEquals(Request $request, array $expected): void
    {
        $requestContent = json_decode($request->getBody()->getContents(), true)[0];
        NormalizedProductCleaner::clean($requestContent['data']['resource']);

        $this->assertEquals($expected, $requestContent);
    }

    private function expectedProductModelUpdatedPayload(): array
    {
        return [
            "action" => "product_model.updated",
            "event_id" => "0d931d13-8eae-4f4a-bf37-33d3a932b8c9",
            "event_date" => "2020-12-04T16:02:47+01:00",
            "author" => "admin",
            "author_type" => "ui",
            "pim_source" => "http://localhost:8080",
            "data" => $this->expectedData(),
        ];
    }

    private function expectedProductModelCreatedPayload(): array
    {
        return [
            "action" => "product_model.created",
            "event_id" => "0d931d13-8eae-4f4a-bf37-33d3a932b8c9",
            "event_date" => "2020-12-04T16:02:47+01:00",
            "author" => "admin",
            "author_type" => "ui",
            "pim_source" => "http://localhost:8080",
            "data" => $this->expectedData(),
        ];
    }

    private function expectedProductModelRemovedPayload(): array
    {
        return [
            "action" => "product_model.removed",
            "event_id" => "0d931d13-8eae-4f4a-bf37-33d3a932b8c9",
            "event_date" => "2020-12-04T16:02:47+01:00",
            "author" => "admin",
            "author_type" => "ui",
            "pim_source" => "http://localhost:8080",
            "data" => [
                "resource" => [
                    "code" => "product_model",
                ],
            ],
        ];
    }

    private function expectedData(): array
    {
        return [
            "resource" => [
                "code" => "product_model",
                "family" => "family",
                "family_variant" => "family_variant",
                "parent" => null,
                "categories" => [],
                "values" => [],
                "created" => "this is a date formatted to ISO-8601",
                "updated" => "this is a date formatted to ISO-8601",
                "associations" => [
                    "PACK" => [
                        "groups" => [],
                        "product_models" => [],
                        "products" => [],
                    ],
                    "SUBSTITUTION" => [
                        "groups" => [],
                        "product_models" => [],
                        "products" => [],
                    ],
                    "UPSELL" => [
                        "groups" => [],
                        "product_models" => [],
                        "products" => [],
                    ],
                    "X_SELL" => [
                        "groups" => [],
                        "product_models" => [],
                        "products" => [],
                    ],
                ],
                "quantified_associations" => [],
            ],
        ];
    }
}
