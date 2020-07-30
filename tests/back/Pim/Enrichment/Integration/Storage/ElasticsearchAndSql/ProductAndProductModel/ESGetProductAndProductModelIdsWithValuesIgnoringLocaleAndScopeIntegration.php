<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\ESGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ESGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScopeIntegration extends TestCase
{
    private const SERVICE_NAME = 'akeneo.pim.enrichment.product.query.get_product_and_product_model_ids_with_values_ignoring_locale_and_scope';

    /** @var ESGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope */
    private $eSGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eSGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope = $this->get(static::SERVICE_NAME);
        $this->eSGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope->setBatchSize(5);
    }

    public function test_it_returns_products_and_product_models_using_pagination()
    {
        $attribute = new Attribute();
        $attribute->setCode('color');
        $attribute->setBackendType('option');
        $results = $this->eSGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope->forAttributeAndValues(
            $attribute,
            ['blue']
        );

        $totalIdentifiers = [];
        foreach ($results as $identifiers) {
            $totalIdentifiers = array_merge($totalIdentifiers, $identifiers);
        }

        $this->assertNotEmpty($totalIdentifiers);
        $this->assertContains('1111111111', $totalIdentifiers);
        $this->assertContains('artemis_blue', $totalIdentifiers);
        $this->assertNotContains('artemis_red', $totalIdentifiers);
    }

    public function test_it_returns_products_and_product_models_for_a_localizable_attribute()
    {
        $attribute = new Attribute();
        $attribute->setCode('name');
        $attribute->setBackendType('text');
        $results = $this->eSGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope->forAttributeAndValues(
            $attribute,
            ['athena']
        );

        $totalIdentifiers = [];
        foreach ($results as $identifiers) {
            $totalIdentifiers = array_merge($totalIdentifiers, $identifiers);
        }

        $this->assertCount(1, $totalIdentifiers);
        $this->assertSame('athena', $totalIdentifiers[0]);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
