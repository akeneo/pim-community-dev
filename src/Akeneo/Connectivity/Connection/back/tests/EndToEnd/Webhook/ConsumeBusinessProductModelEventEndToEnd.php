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
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
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

        $message = new BulkEvent([new ProductModelCreated($author, ['code' => $productModel->getCode()])]);

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);
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

        $message = new BulkEvent([new ProductModelUpdated($author, ['code' => $productModel->getCode()])]);

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);
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

        /** @var HandlerStack $handlerStack*/
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new ProductModelRemoved($author, [
            'code' => $productModel->getCode(),
            'category_codes' => $productModel->getCategoryCodes(),
        ]);

        $message = new BulkEvent([
            new ProductModelRemoved($author, ['code' => $productModel->getCode(), 'category_codes' => $productModel->getCategoryCodes()])
        ]);

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);
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
        $this->attributeLoader->create([
            'code' => 'variant_attribute',
            'type' => 'pim_catalog_boolean',
        ]);

        $this->attributeLoader->create([
            'code' => 'text_attribute',
            'type' => 'pim_catalog_text',
        ]);

        $this->familyLoader->create([
            'code' => 'family',
            'attributes' => ['variant_attribute', 'text_attribute'],
        ]);

        $familyVariant = $this->familyVariantLoader->create([
            'code' => 'family_variant',
            'family' => 'family',
            'variant_attribute_sets' => [
                [
                    'axes' => ['variant_attribute'],
                    'attributes' => [],
                    'level' => 1,
                ],
            ],
        ]);

        return $this->productModelLoader->create([
            'code' => 'product_model',
            'family_variant' => $familyVariant->getCode(),
        ]);
    }
}
