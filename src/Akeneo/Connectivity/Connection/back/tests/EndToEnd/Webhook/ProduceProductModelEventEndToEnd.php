<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\FamilyVariantLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductModelLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\FamilyLoader;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
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

    /** @var AttributeLoader */
    private $attributeLoader;

    /** @var FamilyLoader */
    private $familyLoader;

    /** @var FamilyVariantLoader */
    private $familyVariantLoader;

    /** @var RemoverInterface */
    private $productModelRemover;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productModelLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product_model');
        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->familyVariantLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.family_variant');
        $this->productModelRemover = $this->get('pim_catalog.remover.product_model');
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
        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf(ProductModelRemoved::class, $envelopes[0]->getMessage());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
