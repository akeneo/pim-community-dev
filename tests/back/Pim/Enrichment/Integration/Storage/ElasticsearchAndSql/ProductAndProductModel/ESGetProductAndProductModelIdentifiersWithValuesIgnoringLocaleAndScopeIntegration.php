<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResults;
use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\ESGetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ESGetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScopeIntegration extends TestCase
{
    private const SERVICE_NAME = 'akeneo.pim.enrichment.product.query.get_product_and_product_model_identifiers_with_values_ignoring_locale_and_scope';

    /** @var ESGetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope */
    private $eSGetProductAndProductModelIdentifiers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eSGetProductAndProductModelIdentifiers = $this->get(static::SERVICE_NAME);
        $this->eSGetProductAndProductModelIdentifiers->setBatchSize(5);
    }

    public function test_it_returns_products_and_product_models_using_pagination()
    {
        $results = $this->eSGetProductAndProductModelIdentifiers->forAttributeAndValues(
            'color',
            'option',
            ['blue']
        );

        $allIdentifierResults = is_array($results) ? $results : iterator_to_array($results);
        Assert::assertContainsOnlyInstancesOf(IdentifierResults::class, $allIdentifierResults);
        $productIdentifiers = [];
        $productModelCodes = [];
        foreach ($allIdentifierResults as $identifierResult) {
            $productIdentifiers = array_merge(
                $productIdentifiers,
                $identifierResult->getProductIdentifiers()
            );
            $productModelCodes = array_merge(
                $productModelCodes,
                $identifierResult->getProductModelIdentifiers()
            );
        }

        $this->assertNotEmpty($productIdentifiers);
        $this->assertNotEmpty($productModelCodes);
        $this->assertContains('1111111111', $productIdentifiers);
        $this->assertContains('artemis_blue', $productModelCodes);
        $this->assertNotContains('artemis_red', $productModelCodes);
    }

    public function test_it_returns_products_and_product_models_for_a_localizable_attribute()
    {
        $results = $this->eSGetProductAndProductModelIdentifiers->forAttributeAndValues(
            'name',
            'text',
            ['athena']
        );

        $allIdentifierResults = is_array($results) ? $results : iterator_to_array($results);
        Assert::assertContainsOnlyInstancesOf(IdentifierResults::class, $allIdentifierResults);
        $productIdentifiers = [];
        $productModelCodes = [];
        foreach ($allIdentifierResults as $identifierResult) {
            $productIdentifiers = array_merge(
                $productIdentifiers,
                $identifierResult->getProductIdentifiers()
            );
            $productModelCodes = array_merge(
                $productModelCodes,
                $identifierResult->getProductModelIdentifiers()
            );
        }

        $this->assertEmpty($productIdentifiers);
        $this->assertSame(['athena'], $productModelCodes);
    }

    public function test_it_returns_all_products_and_models_with_non_empty_values_if_provided_options_are_empty()
    {
        $results = $this->eSGetProductAndProductModelIdentifiers->forAttributeAndValues(
            'size',
            'option',
            []
        );

        $allIdentifierResults = is_array($results) ? $results : iterator_to_array($results);
        Assert::assertContainsOnlyInstancesOf(IdentifierResults::class, $allIdentifierResults);
        $productIdentifiers = [];
        $productModelCodes = [];
        foreach ($allIdentifierResults as $identifierResult) {
            $productIdentifiers = array_merge(
                $productIdentifiers,
                $identifierResult->getProductIdentifiers()
            );
            $productModelCodes = array_merge(
                $productModelCodes,
                $identifierResult->getProductModelIdentifiers()
            );
        }

        Assert::assertContains('1111111111', $productIdentifiers);
        Assert::assertNotContains('1111111173', $productIdentifiers);
        Assert::assertContains('tshirt-divided-navy-blue-xxs', $productIdentifiers);
        Assert::assertNotContains('artemis_red', $productModelCodes);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
