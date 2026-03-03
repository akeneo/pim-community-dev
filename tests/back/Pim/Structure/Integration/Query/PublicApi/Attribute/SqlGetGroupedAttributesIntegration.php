<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Query\PublicApi\Attribute;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\SqlGetGroupedAttributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetGroupedAttributesIntegration extends TestCase
{
    public function test_it_returns_only_given_attribute_types(): void
    {
        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'en_US',
            100,
            0,
            ['pim_catalog_text']
        );
        $this->assertNotNull($this->findAttributeInResults('erp', 'erp_name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'name', $results));
        $this->assertNull($this->findAttributeInResults('marketing', 'description', $results));

        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'en_US',
            100,
            0,
            ['pim_catalog_number', 'pim_catalog_textarea']
        );
        $this->assertNull($this->findAttributeInResults('erp', 'erp_name', $results));
        $this->assertNull($this->findAttributeInResults('marketing', 'name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'description', $results));
    }

    public function test_it_returns_paginate_results(): void
    {
        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'en_US',
            4,
            1,
            ['pim_catalog_text', 'pim_catalog_number', 'pim_catalog_textarea']
        );
        $this->assertCount(4, $results);
        $codeForFirstResult = $results[0]['code'];

        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'en_US',
            4,
            2,
            ['pim_catalog_text', 'pim_catalog_number', 'pim_catalog_textarea']
        );
        $this->assertCount(4, $results);
        $this->assertNotEquals($codeForFirstResult, $results[0]['code']);

        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'en_US',
            4,
            200,
            ['pim_catalog_text', 'pim_catalog_number', 'pim_catalog_textarea'],
        );
        $this->assertCount(0, $results);
    }

    public function test_it_searches_attribute_by_name(): void
    {
        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'en_US',
            4,
            1,
            ['pim_catalog_text', 'pim_catalog_number', 'pim_catalog_textarea'],
            'eSCript'
        );
        $this->assertNotEmpty($results);
        $this->assertNotNull($this->findAttributeInResults('marketing', 'description', $results));

        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'en_US',
            4,
            1,
            ['pim_catalog_text', 'pim_catalog_number'],
            'eSCript'
        );
        $this->assertNull($this->findAttributeInResults('marketing', 'description', $results));
    }

    public function test_it_uses_the_locale_code_for_labels(): void
    {
        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'fr_FR',
            100,
            0,
            ['pim_catalog_text', 'pim_catalog_number', 'pim_catalog_textarea']
        );
        $this->assertNotEmpty($results);
        $erpNameAttribute = $this->findAttributeInResults('erp', 'erp_name', $results);
        $this->assertNotNull($erpNameAttribute);
        $this->assertSame('Nom ERP', $erpNameAttribute['label']);
        $topCompositionAttribute = $this->findAttributeInResults('product', 'top_composition', $results);
        $this->assertNotNull($topCompositionAttribute);
        $this->assertSame('Composition dessus', $topCompositionAttribute['label']);
        $this->assertSame('Produit', $topCompositionAttribute['group_label']);

        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'unnown',
            100,
            0,
            ['pim_catalog_text', 'pim_catalog_number', 'pim_catalog_textarea']
        );
        $this->assertNotEmpty($results);
        $erpNameAttribute = $this->findAttributeInResults('erp', 'erp_name', $results);
        $this->assertNotNull($erpNameAttribute);
        $this->assertSame('[erp_name]', $erpNameAttribute['label']);
        $this->assertSame('[erp]', $erpNameAttribute['group_label']);
    }

    public function test_it_returns_everything(): void
    {
        $results = $this->getSqlGetGroupedAttributes()->findAttributes(
            'fr_FR',
            100
        );
        $this->assertNotEmpty($results);
        $this->assertNotNull($this->findAttributeInResults('erp', 'erp_name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'brand', $results));
    }

    private function findAttributeInResults(string $attributeGroupCode, string $attributeCode, array $results): ?array
    {
        foreach ($results as $result) {
            if ($attributeGroupCode === $result['group_code'] && $attributeCode === $result['code']) {
                return $result;
            }
        }

        return null;
    }

    private function getSqlGetGroupedAttributes(): SqlGetGroupedAttributes
    {
        return $this->get('Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetGroupedAttributes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
