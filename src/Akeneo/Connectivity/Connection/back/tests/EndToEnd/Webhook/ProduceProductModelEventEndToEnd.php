<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\FamilyVariantLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductModelLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProduceProductModelEventEndToEnd extends ApiTestCase
{
    /** @var ProductModelLoader */
    private $productModelLoader;

    /** @var FamilyVariantLoader */
    private $familyVariantLoader;

    /** @var FamilyLoader */
    private $familyLoader;

    /** @var AttributeLoader */
    private $attributeLoader;

    /** @var RemoverInterface */
    private $productModelRemover;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->familyVariantLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.family_variant');
        $this->productModelLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product_model');
        $this->productModelRemover = $this->get('pim_catalog.remover.product_model');
    }

    public function test_create_product_model_add_business_event_to_queue()
    {
        $this->attributeLoader->create(
            [
                'code' => 'variant',
                'type' => 'pim_catalog_boolean',
            ]
        );

        $this->attributeLoader->create(
            [
                'code' => 'text',
                'type' => 'pim_catalog_text',
            ]
        );

        $this->familyLoader->create(
            [
                'code' => 'family',
                'attributes' => ['variant', 'text'],
            ]
        );

        $familyVariant = $this->familyVariantLoader->create(
            [
                'code' => 'family_variant',
                'family' => 'family',
                'variant_attribute_sets' => [
                    [
                        'axes' => ['variant'],
                        'attributes' => [],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION);
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $data =
            <<<JSON
    {
        "code": "product_model",
        "family": "family",
        "family_variant": "family_variant",
        "values": {
        }
    }
JSON;

        $apiClient->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $transport = self::$container->get('messenger.transport.business_event');
        $envelopes = $transport->get();

        $this->assertCount(1, $envelopes);

        /** @var BulkEvent */
        $bulkEvent = $envelopes[0]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertContainsOnlyInstancesOf(ProductModelCreated::class, $bulkEvent->getEvents());
    }

    public function test_update_product_model_add_business_event_to_queue()
    {
        $this->attributeLoader->create(
            [
                'code' => 'variant',
                'type' => 'pim_catalog_boolean',
            ]
        );

        $this->attributeLoader->create(
            [
                'code' => 'text',
                'type' => 'pim_catalog_text',
            ]
        );

        $this->familyLoader->create(
            [
                'code' => 'family',
                'attributes' => ['variant', 'text'],
            ]
        );

        $familyVariant = $this->familyVariantLoader->create(
            [
                'code' => 'family_variant',
                'family' => 'family',
                'variant_attribute_sets' => [
                    [
                        'axes' => ['variant'],
                        'attributes' => [],
                        'level' => 1,
                    ],
                ],
            ]
        );

        $productModel = $this->productModelLoader->create(
            ['code' => 'product_model', 'family_variant' => $familyVariant->getCode(),]
        );

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION);
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $data =
            <<<JSON
    {
        "code": "product_model",
        "family": "family",
        "family_variant": "family_variant",
        "values": {
            "text": [
                {"locale": null, "scope": null, "data": "Lorem ipsum"}
            ]
        }
    }
JSON;
        $apiClient->request('PATCH', 'api/rest/v1/product-models/product_model', [], [], [], $data);

        $transport = self::$container->get('messenger.transport.business_event');
        $envelopes = $transport->get();

        $this->assertCount(1, $envelopes);

        /** @var BulkEvent */
        $bulkEvent = $envelopes[0]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertContainsOnlyInstancesOf(ProductModelUpdated::class, $bulkEvent->getEvents());
    }

    public function test_remove_product_model_add_business_event_to_queue()
    {
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

        $this->productModelRemover->remove($productModel);

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();

        $this->assertCount(2, $envelopes);

        $this->assertInstanceOf(BulkEvent::class, $envelopes[0]->getMessage());
        $this->assertContainsOnlyInstancesOf(ProductModelCreated::class, $envelopes[0]->getMessage()->getEvents());

        $this->assertInstanceOf(BulkEvent::class, $envelopes[1]->getMessage());
        $this->assertContainsOnlyInstancesOf(ProductModelRemoved::class, $envelopes[1]->getMessage()->getEvents());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
