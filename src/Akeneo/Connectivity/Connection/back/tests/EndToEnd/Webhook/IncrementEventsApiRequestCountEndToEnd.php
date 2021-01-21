<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\CategoryLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IncrementEventsApiRequestCountEndToEnd extends ApiTestCase
{
    private ProductLoader $productLoader;
    private CategoryLoader $categoryLoader;
    private FamilyLoader $familyLoader;
    private AttributeLoader $attributeLoader;
    private ProductInterface $referenceProduct;
    private Author $referenceAuthor;
    private DbalConnection $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
        $this->categoryLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.category');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->dbalConnection = $this->get('database_connection');

        $this->referenceProduct = $this->loadReferenceProduct();
        $this->referenceAuthor = Author::fromNameAndType('julia', Author::TYPE_UI);

        $connection = $this->get('akeneo_connectivity.connection.fixtures.connection_loader')->createConnection(
            'ecommerce',
            'Ecommerce',
            FlowType::DATA_DESTINATION,
            false,
        );

        $this->get('akeneo_connectivity.connection.fixtures.webhook_loader')->initWebhook($connection->code());
    }

    public function test_it_increments_events_api_request_count()
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

        $eventsApiRequestCount = $this->getEventsApiRequestCount();

        Assert::assertCount(1, $eventsApiRequestCount);
        Assert::assertEquals(1, $eventsApiRequestCount[0]['event_count']);
    }

    private function loadReferenceProduct(): ProductInterface
    {
        $this->categoryLoader->create(['code' => 'category']);
        $this->attributeLoader->create(['code' => 'boolean_attribute', 'type' => 'pim_catalog_boolean']);
        $this->attributeLoader->create(['code' => 'text_attribute', 'type' => 'pim_catalog_text']);
        $this->attributeLoader->create(['code' => 'another_text_attribute', 'type' => 'pim_catalog_text']);
        $this->familyLoader->create(['code' => 'family', 'attributes' => ['boolean_attribute', 'text_attribute']]);

        return $this->productLoader->create(
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

    private function getEventsApiRequestCount(): array
    {
        $sql = <<<SQL
SELECT event_minute, event_count, updated
FROM akeneo_connectivity_connection_events_api_request_count
SQL;

        return $this->dbalConnection->executeQuery($sql)->fetchAll();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
