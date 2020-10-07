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
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConsumeProductModelEventEndToEnd extends ApiTestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var WebhookLoader */
    private $webhookLoader;

    /** @var ProductModelLoader */
    private $productModelLoader;

    /** @var AttributeLoader */
    private $attributeLoader;

    /** @var FamilyLoader */
    private $familyLoader;

    /** @var FamilyVariantLoader */
    private $familyVariantLoader;

    /** @var NormalizerInterface */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->productModelLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product_model');
        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->familyVariantLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.family_variant');
        $this->normalizer = $this->get('pim_catalog.normalizer.standard.product_model');
    }

    public function test_it_sends_a_product_model_removed_webhook_event()
    {
        $connection = $this->connectionLoader->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION, false);
        $this->webhookLoader->initWebhook($connection->code());

        $this->attributeLoader->create(
            [
                'code' => 'test_variant_attribute',
                'type' => 'pim_catalog_boolean',
            ]
        );
        $this->familyLoader->create(
            [
                'code' => 'test_family',
                'attributes' => ['test_variant_attribute'],
            ]
        );
        $this->familyVariantLoader->create(
            [
                'code' => 'test_family_variant',
                'family' => 'test_family',
                'variant_attribute_sets' => [
                    [
                        'axes' => ['test_variant_attribute'],
                        'attributes' => [],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $productModel = $this->productModelLoader->create(
            ['code' => 'test_product_model', 'family_variant' => 'test_family_variant',]
        );

        /** @var HandlerStack $handlerStack*/
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([
            new Response(200),
        ]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new ProductModelRemoved(
            'ecommerce',
            $this->normalizer->normalize($productModel, 'standard')
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
