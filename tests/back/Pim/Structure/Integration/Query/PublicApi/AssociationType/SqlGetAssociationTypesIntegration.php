<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Query\PublicApi\AssociationType;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association\Sql\SqlGetAssociationTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlGetAssociationTypesIntegration extends TestCase
{
    public function test_it_returns_association_types_depending_on_search(): void
    {
        $results = $this->getSqlGetAssociationTypes()->execute('fr_FR', 100);

        $this->assertNotNull($this->findAssociationInResults('PACK', $results));
        $this->assertNotNull($this->findAssociationInResults('X_SELL', $results));
        $this->assertNotNull($this->findAssociationInResults('SUBSTITUTION', $results));
        $this->assertNotNull($this->findAssociationInResults('UPSELL', $results));

        $results = $this->getSqlGetAssociationTypes()->execute('fr_FR', 100, 0, 'substitu');

        $this->assertNull($this->findAssociationInResults('PACK', $results));
        $this->assertNull($this->findAssociationInResults('X_SELL', $results));
        $this->assertNotNull($this->findAssociationInResults('SUBSTITUTION', $results));
        $this->assertNull($this->findAssociationInResults('UPSELL', $results));

        $results = $this->getSqlGetAssociationTypes()->execute('fr_FR', 100, 0, 'Vente');

        $this->assertNull($this->findAssociationInResults('PACK', $results));
        $this->assertNotNull($this->findAssociationInResults('X_SELL', $results));
        $this->assertNull($this->findAssociationInResults('SUBSTITUTION', $results));
        $this->assertNotNull($this->findAssociationInResults('UPSELL', $results));
    }

    public function test_it_returns_paginate_results(): void
    {
        $results = $this->getSqlGetAssociationTypes()->execute('en_US', 2, 1);
        $this->assertCount(2, $results);
        $codeForFirstResult = $results[0]->code;

        $results = $this->getSqlGetAssociationTypes()->execute('en_US', 2, 2);
        $this->assertCount(2, $results);
        $this->assertNotEquals($codeForFirstResult, $results[0]->code);

        $results = $this->getSqlGetAssociationTypes()->execute('en_US', 2, 200);
        $this->assertCount(0, $results);
    }

    public function test_it_uses_the_locale_code_for_labels(): void
    {
        $results = $this->getSqlGetAssociationTypes()->execute('fr_FR', 100);
        $this->assertNotEmpty($results);

        $association = $this->findAssociationInResults('X_SELL', $results);
        $this->assertNotNull($association);
        $this->assertSame('Vente croisée', $association->label);
        $association = $this->findAssociationInResults('UPSELL', $results);
        $this->assertNotNull($association);
        $this->assertSame('Vente incitative', $association->label);

        $results = $this->getSqlGetAssociationTypes()->execute('unknown', 100);
        $this->assertNotEmpty($results);

        $association = $this->findAssociationInResults('X_SELL', $results);
        $this->assertNotNull($association);
        $this->assertSame('[X_SELL]', $association->label);
    }

    /**
     * @param AssociationType[] $results
     */
    private function findAssociationInResults(string $associationTypeCode, array $results): ?AssociationType
    {
        foreach ($results as $result) {
            if ($associationTypeCode === $result->code) {
                return $result;
            }
        }

        return null;
    }

    private function getSqlGetAssociationTypes(): SqlGetAssociationTypes
    {
        return $this->get('akeneo.pim.structure.query.get_association_types');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
