<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\WebhookLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConsumeBusinessProductEventEndToEnd extends ApiTestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var WebhookLoader */
    private $webhookLoader;

    /** @var ProductLoader */
    private $productLoader;

    /** @var NormalizerInterface */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
        $this->normalizer = $this->get('pim_catalog.normalizer.standard.product');
    }

    public function test_it_sends_a_product_created_webhook_event()
    {
        $author = Author::fromUser($this->createAdminUser());
        $connection = $this->connectionLoader->createConnection(
            'ecommerce',
            'Ecommerce',
            FlowType::DATA_DESTINATION,
            false
        );

        $this->webhookLoader->initWebhook($connection->code());
        $product = $this->productLoader->create('product_created_test', []);

        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(
            new MockHandler(
                [
                    new Response(200),
                ]
            )
        );

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);
        $message = new BulkEvent([
            new ProductCreated(
                $author,
                $this->normalizer->normalize($product, 'standard')
            )
        ]);

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        Assert::assertCount(1, $container);
    }

    public function test_it_sends_a_product_updated_webhook_event()
    {
        $author = Author::fromUser($this->createAdminUser());
        $connection = $this->connectionLoader->createConnection(
            'ecommerce',
            'Ecommerce',
            FlowType::DATA_DESTINATION,
            false
        );
        $this->webhookLoader->initWebhook($connection->code());
        $product = $this->productLoader->create('product_updated_test', []);

        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(
            new MockHandler(
                [
                    new Response(200),
                ]
            )
        );

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent([
            new ProductUpdated(
                $author,
                $this->normalizer->normalize($product, 'standard')
            )
        ]);

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        Assert::assertCount(1, $container);
    }

    public function test_it_sends_a_product_removed_webhook_event()
    {
        $author = Author::fromUser($this->createAdminUser());
        $connection = $this->connectionLoader->createConnection(
            'ecommerce',
            'Ecommerce',
            FlowType::DATA_DESTINATION,
            false
        );
        $this->webhookLoader->initWebhook($connection->code());
        $product = $this->productLoader->create('product_to_remove_test', []);

        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(
            new MockHandler(
                [
                    new Response(200),
                ]
            )
        );

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new ProductRemoved(
            $author,
            $this->normalizer->normalize($product, 'standard')
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
