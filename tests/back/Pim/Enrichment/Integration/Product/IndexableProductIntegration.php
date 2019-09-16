<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\IndexableProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * Class IndexableProductIntegration
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexableProductIntegration extends TestCase
{
    /**
     * This test checks that the array provided by IndexableProduct::toArray() class is equal
     * to the result of the product normalization.
     * IndexableProduct::fromProductReadModel() is a temporary method (see TIP-1222), so this test is temporary too.
     */
    public function testFromProductReadModel(): void
    {
        $identifiers = [
            '1111111111',
            '1111111112',
            '1111111113',
            '1111111114',
            '1111111115',
            '1111111116',
            '1111111117',
            '1111111118',
            '1111111119',
            '1111111120',
        ];

        foreach ($identifiers as $identifier) {
            $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
            $this->assertInstanceOf(ProductInterface::class, $product);

            $this->runTestFromProductReadModelWithProduct($product);
        }
    }

    private function runTestFromProductReadModelWithProduct(ProductInterface $product)
    {
        $normalizedValues = $this->get('pim_indexing_serializer')->normalize(
            $product->getValues(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        );

        $indexableProduct = IndexableProduct::fromProductReadModel(
            $product,
            $this->get('pim_catalog.repository.locale')->getActivatedLocaleCodes(),
            $this->get('pim_catalog.repository.channel')->getChannelCodes(),
            $normalizedValues,
            $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')->fromProductId(
                $product->getId()
            ),
            $this->get('pim_catalog.family_variant.provider.entity_with_family_variant_attributes')
        );

        $this->assertEquals(
            $this->get('pim_indexing_serializer')->normalize(
                $product,
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            ),
            $indexableProduct->toArray()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
