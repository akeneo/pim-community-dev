<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductModelLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\FamilyVariantLoader;
use Akeneo\Pim\Enrichment\Component\ContextOrigin;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventOriginTrait;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProduceProductModelEventEndToEnd extends ApiTestCase
{
    use AssertEventCountTrait;
    use AssertEventOriginTrait;

    private ProductModelLoader $productModelLoader;
    private FamilyVariantLoader $familyVariantLoader;
    private FamilyLoader $familyLoader;
    private AttributeLoader $attributeLoader;
    private RemoverInterface $productModelRemover;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->familyVariantLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family_variant');
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

        $this->assertEventCount(1, ProductModelCreated::class);
        $this->assertEventOrigin(ContextOrigin::API);
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

        $this->assertEventCount(1, ProductModelUpdated::class);
        $this->assertEventOrigin(ContextOrigin::API);
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

        $this->assertEventCount(1, ProductModelRemoved::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
