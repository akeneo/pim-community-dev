<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Query\PublicApi\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindFlattenAttributesIntegrationTest extends TestCase
{
    public function test_it_returns_only_given_attribute_types(): void
    {
        $results = $this->getQuery()->execute(
            'en_US',
            100,
            ['pim_catalog_text'],
        );
        $this->assertNotNull($this->findAttributeInResults('erp', 'erp_name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'name', $results));
        $this->assertNull($this->findAttributeInResults('marketing', 'description', $results));

        $results = $this->getQuery()->execute(
            'en_US',
            100,
            ['pim_catalog_number', 'pim_catalog_textarea']
        );
        $this->assertNull($this->findAttributeInResults('erp', 'erp_name', $results));
        $this->assertNull($this->findAttributeInResults('marketing', 'name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'description', $results));
    }

    public function test_it_returns_attribute_depending_on_search(): void
    {
        $results = $this->getQuery()->execute('en_US', 100);

        $this->assertNotNull($this->findAttributeInResults('erp', 'erp_name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'description', $results));

        $results = $this->getQuery()->execute('en_US', 100, null, 0, 'descr');

        $this->assertNull($this->findAttributeInResults('erp', 'erp_name', $results));
        $this->assertNull($this->findAttributeInResults('marketing', 'name', $results));
        $this->assertNotNull($this->findAttributeInResults('marketing', 'description', $results));
    }

    public function test_it_returns_paginate_results(): void
    {
        $results = $this->getQuery()->execute('en_US', 4, null, 1);
        $this->assertCount(4, $results);
        $codeForFirstResult = $results[0]->getCode();

        $results = $this->getQuery()->execute('en_US', 4, null, 2);
        $this->assertCount(4, $results);
        $this->assertNotEquals($codeForFirstResult, $results[0]->getCode());

        $results = $this->getQuery()->execute('en_US', 4, null, 200);
        $this->assertCount(0, $results);
    }

    public function test_it_uses_the_locale_code_for_labels(): void
    {
        $results = $this->getQuery()->execute('fr_FR', 100);
        $this->assertNotEmpty($results);
        $erpNameAttribute = $this->findAttributeInResults('erp', 'erp_name', $results);
        $this->assertNotNull($erpNameAttribute);
        $this->assertSame('Nom ERP', $erpNameAttribute->getLabel());
        $topCompositionAttribute = $this->findAttributeInResults('product', 'top_composition', $results);
        $this->assertNotNull($topCompositionAttribute);
        $this->assertSame('Composition dessus', $topCompositionAttribute->getLabel());
        $this->assertSame('Produit', $topCompositionAttribute->getAttributeGroupLabel());

        $results = $this->getQuery()->execute('unnown', 100);
        $this->assertNotEmpty($results);
        $erpNameAttribute = $this->findAttributeInResults('erp', 'erp_name', $results);
        $this->assertNotNull($erpNameAttribute);
        $this->assertSame('[erp_name]', $erpNameAttribute->getLabel());
        $this->assertSame('[erp]', $erpNameAttribute->getAttributeGroupLabel());
    }

    /**
     * @param FlattenAttribute[] $results
     */
    private function findAttributeInResults(string $attributeGroupCode, string $attributeCode, array $results): ?FlattenAttribute
    {
        foreach ($results as $result) {
            if ($attributeGroupCode === $result->getAttributeGroupCode() && $attributeCode === $result->getCode()) {
                return $result;
            }
        }

        return null;
    }

    private function getQuery(): FindFlattenAttributesInterface
    {
        return $this->get('akeneo.pim.structure.query.find_flatten_attributes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
