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

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
            ->map('asin', $this->getAttribute('asin'))
            ->map('upc', $this->getAttribute('ean'))
            ->map('mpn', $this->getAttribute('pim_mpn'))
            ->map('brand', $this->getAttribute('pim_brand'));
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
            $this->getIdentifierValues($product->getId()),
            [
                'asin' => 'ABC123',
                'upc' => '133214',
                'mpn' => 'pim',
                'brand' => 'akeneo',
            ]
        );
    }

    public function test_that_it_convert_null_json_raw_values_as_null(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping
            ->map('asin', $this->getAttribute('asin'))
            ->map('upc', $this->getAttribute('ean'))
            ->map('mpn', $this->getAttribute('pim_mpn'))
            ->map('brand', $this->getAttribute('pim_brand'));
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
            $this->getIdentifierValues($product->getId()),
            [
                'asin' => 'ABC123',
                'upc' => '133214',
                'mpn' => null,
                'brand' => null,
            ]
        );
    }

    public function test_that_it_returns_null_if_the_product_does_not_exist(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping->map('upc', $this->getAttribute('ean'));
        $this->saveMapping($mapping);

        Assert::assertNull($this->getIdentifierValues(42));
    }

    public function test_that_it_returns_null_if_there_is_no_identifier_mapping(): void
    {
        $product = $this->createProduct('some_sku', []);
        Assert::assertNull($this->getIdentifierValues($product->getId()));
    }

    public function test_that_it_completes_unmapped_identifiers(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping->map('upc', $this->getAttribute('ean'));
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
            $this->getIdentifierValues($product->getId()),
            [
                'asin' => null,
                'upc' => '133214',
                'mpn' => null,
                'brand' => null,
            ]
        );
    }

    public function test_that_it_completes_missing_identifier_values(): void
    {
        $mapping = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.identifiers_mapping')
                        ->find();
        $mapping
            ->map('asin', $this->getAttribute('asin'))
            ->map('upc', $this->getAttribute('ean'))
            ->map('mpn', $this->getAttribute('pim_mpn'))
            ->map('brand', $this->getAttribute('pim_brand'));
        $this->saveMapping($mapping);

        $product = $this->createProduct('some_sku', []);

        $this->assertIdentifierValues(
            $this->getIdentifierValues($product->getId()),
            [
                'asin' => null,
                'upc' => null,
                'mpn' => null,
                'brand' => null,
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
     * @param int $productId
     *
     * @return ProductIdentifierValues|null
     */
    private function getIdentifierValues(int $productId): ?ProductIdentifierValues
    {
        return $this->getFromTestContainer(
            sprintf(
                '%s.%s',
                'akeneo.pim.automation.franklin_insights',
                'infrastructure.persistence.query.product.select_product_identifier_values_query'
            )
        )->execute($productId);
    }

    /**
     * @param mixed $values
     * @param array $expected
     */
    private function assertIdentifierValues($values, array $expected): void
    {
        Assert::assertInstanceOf(ProductIdentifierValues::class, $values);
        $identifierValues = $values->identifierValues();
        Assert::assertSame(count($expected), count($identifierValues));
        foreach ($expected as $key => $expectedValue) {
            Assert::assertArrayHasKey($key, $identifierValues);
            Assert::assertSame($expectedValue, $identifierValues[$key]);
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

    /**
     * @param string $name
     *
     * @return AttributeInterface|null
     */
    private function getAttribute(string $name): ?AttributeInterface
    {
        return $this->getFromTestContainer('pim_catalog.repository.attribute')->findOneByIdentifier($name);
    }
}
