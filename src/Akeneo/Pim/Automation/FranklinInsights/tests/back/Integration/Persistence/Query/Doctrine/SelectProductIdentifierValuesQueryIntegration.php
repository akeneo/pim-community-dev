<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SelectProductIdentifierValuesQueryIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTextAttribute('asin');
        $this->createTextAttribute('ean');
        $this->createTextAttribute('pim_brand');
        $this->createTextAttribute('pim_mpn');
    }

    public function test_that_it_retrieves_product_identifier_values(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping
            ->map('asin', new AttributeCode('asin'))
            ->map('upc', new AttributeCode('ean'))
            ->map('mpn', new AttributeCode('pim_mpn'))
            ->map('brand', new AttributeCode('pim_brand'));
        $this->saveMapping($mapping);

        $product = $this->createProduct(
            'some_sku',
            [
                'ean' => '133214',
                'asin' => 'ABC123',
                'pim_mpn' => 'pim',
                'pim_brand' => 'akeneo',
            ]
        );
        $otherProduct = $this->createProduct(
            'other_sku',
            [
                'ean' => '9876543210',
            ]
        );

        $this->assertIdentifierValues(
            $this->getIdentifierValues([new ProductId($product->getId()), new ProductId($otherProduct->getId())]),
            [
                $product->getId() => [
                    'asin' => 'ABC123',
                    'upc' => '133214',
                    'mpn' => 'pim',
                    'brand' => 'akeneo',
                ],
                $otherProduct->getId() => [
                    'asin' => null,
                    'upc' => '9876543210',
                    'mpn' => null,
                    'brand' => null,
                ],
            ]
        );
    }

    public function test_that_it_convert_null_json_raw_values_as_null(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping
            ->map('asin', new AttributeCode('asin'))
            ->map('upc', new AttributeCode('ean'))
            ->map('mpn', new AttributeCode('pim_mpn'))
            ->map('brand', new AttributeCode('pim_brand'));
        $this->saveMapping($mapping);

        $product = $this->createProduct(
            'some_sku',
            [
                'ean' => '133214',
                'asin' => 'ABC123',
                'pim_mpn' => 'pim',
                'pim_brand' => 'akeneo',
            ]
        );

        // Update non null values to null
        // this allows to actually have null json values in the product's raw_values
        // (as non-existing empty values are filtered by the updater)
        $this->updateProductValues(
            $product,
            [
                'pim_mpn' => [['locale' => null, 'scope' => null, 'data' => null]],
                'pim_brand' => [['locale' => null, 'scope' => null, 'data' => null]],
            ]
        );

        $this->assertIdentifierValues(
            $this->getIdentifierValues([new ProductId($product->getId())]),
            [
                $product->getId() => [
                    'asin' => 'ABC123',
                    'upc' => '133214',
                    'mpn' => null,
                    'brand' => null,
                ],
            ]
        );
    }

    public function test_that_it_filters_non_existing_products(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $product = $this->createProduct(
            'some_sku',
            [
                'ean' => '133214',
                'asin' => 'ABC123',
                'pim_mpn' => 'pim',
                'pim_brand' => 'akeneo',
            ]
        );
        $mapping->map('upc', new AttributeCode('ean'));
        $this->saveMapping($mapping);

        $nonExistingProductId = $product->getId() + 10;

        $result = $this->getIdentifierValues([new ProductId($product->getId()), new ProductId($nonExistingProductId)]);

        Assert::assertInstanceOf(ProductIdentifierValues::class, $result->get(new ProductId($product->getId())));
        Assert::assertNull($result->get(new ProductId($nonExistingProductId)));
    }

    public function test_that_it_returns_an_empty_collection_if_there_is_no_identifier_mapping(): void
    {
        $product = $this->createProduct(
            'some_sku',
            [
                'ean' => '133214',
                'asin' => 'ABC123',
                'pim_mpn' => 'pim',
                'pim_brand' => 'akeneo',
            ]
        );

        Assert::assertSame(0, $this->getIdentifierValues([new ProductId($product->getId())])->count());
    }

    public function test_that_it_completes_unmapped_identifiers_with_null(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping->map('upc', new AttributeCode('ean'));
        $this->saveMapping($mapping);

        $product = $this->createProduct(
            'some_sku',
            [
                'ean' => '133214',
                'asin' => 'ABC123',
                'pim_mpn' => 'pim',
                'pim_brand' => 'akeneo',
            ]
        );

        $this->assertIdentifierValues(
            $this->getIdentifierValues([new ProductId($product->getId())]),
            [
                $product->getId() => [
                    'asin' => null,
                    'upc' => '133214',
                    'mpn' => null,
                    'brand' => null,
                ],
            ]
        );
    }

    public function test_that_it_completes_missing_identifier_values_with_null(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping
            ->map('asin', new AttributeCode('asin'))
            ->map('upc', new AttributeCode('ean'))
            ->map('mpn', new AttributeCode('pim_mpn'))
            ->map('brand', new AttributeCode('pim_brand'));
        $this->saveMapping($mapping);

        $product = $this->createProduct('some_sku', []);

        $this->assertIdentifierValues(
            $this->getIdentifierValues([new ProductId($product->getId())]),
            [
                $product->getId() => [
                    'asin' => null,
                    'upc' => null,
                    'mpn' => null,
                    'brand' => null,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param int[] $productIds
     *
     * @return ProductIdentifierValuesCollection
     */
    private function getIdentifierValues(array $productIds): ProductIdentifierValuesCollection
    {
        return $this->getFromTestContainer(
            sprintf(
                '%s.%s',
                'akeneo.pim.automation.franklin_insights',
                'infrastructure.persistence.query.product.select_product_identifier_values_query'
            )
        )->execute($productIds);
    }

    /**
     * @param ProductIdentifierValuesCollection $actual
     * @param array $expected
     */
    private function assertIdentifierValues(ProductIdentifierValuesCollection $actual, array $expected): void
    {
        foreach ($expected as $productId => $expectedValues) {
            $actualValues = $actual->get(new ProductId($productId));
            Assert::assertInstanceOf(ProductIdentifierValues::class, $actualValues);
            foreach ($expectedValues as $franklinCode => $expectedValue) {
                Assert::assertSame($expectedValue, $actualValues->getValue($franklinCode));
            }
        }
    }

    /**
     * @param string $code
     */
    private function createTextAttribute(string $code): void
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_text',
                'group' => 'other',
            ]
        );
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param string $identifier
     * @param array $values
     *
     * @return ProductInterface
     */
    private function createProduct(string $identifier, array $values): ProductInterface
    {
        $builder = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
                        ->withIdentifier($identifier);
        foreach ($values as $attrCode => $data) {
            $builder->withValue($attrCode, $data);
        }
        $product = $builder->build();
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param $values
     */
    private function updateProductValues(ProductInterface $product, array $values): void
    {
        $this->getFromTestContainer('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);
    }

    /**
     * @param IdentifiersMapping $mapping
     */
    private function saveMapping(IdentifiersMapping $mapping): void
    {
        $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')->save(
            $mapping
        );
    }
}
