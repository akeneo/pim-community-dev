<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\FamilyVariantLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductModelLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->familyVariantLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.family_variant');
        $this->productModelLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product_model');
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
        $this->assertInstanceOf(ProductModelUpdated::class, $envelopes[0]->getMessage());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
