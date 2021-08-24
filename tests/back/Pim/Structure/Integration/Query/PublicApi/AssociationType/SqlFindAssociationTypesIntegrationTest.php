<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Query\PublicApi\AssociationType;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association\Sql\SqlFindAssociationTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindAssociationTypesIntegrationTest extends TestCase
{
    public function test_it_returns_association_types_depending_on_search(): void
    {
        $results = $this->getQuery()->execute('fr_FR', 100);

        $this->assertNotNull($this->findAssociationTypeInResults('PACK', $results));
        $this->assertNotNull($this->findAssociationTypeInResults('X_SELL', $results));
        $this->assertNotNull($this->findAssociationTypeInResults('SUBSTITUTION', $results));
        $this->assertNotNull($this->findAssociationTypeInResults('UPSELL', $results));

        $results = $this->getQuery()->execute('fr_FR', 100, 0, 'substitu');

        $this->assertNull($this->findAssociationTypeInResults('PACK', $results));
        $this->assertNull($this->findAssociationTypeInResults('X_SELL', $results));
        $this->assertNotNull($this->findAssociationTypeInResults('SUBSTITUTION', $results));
        $this->assertNull($this->findAssociationTypeInResults('UPSELL', $results));

        $results = $this->getQuery()->execute('fr_FR', 100, 0, 'Vente');

        $this->assertNull($this->findAssociationTypeInResults('PACK', $results));
        $this->assertNotNull($this->findAssociationTypeInResults('X_SELL', $results));
        $this->assertNull($this->findAssociationTypeInResults('SUBSTITUTION', $results));
        $this->assertNotNull($this->findAssociationTypeInResults('UPSELL', $results));
    }

    public function test_it_returns_paginate_results(): void
    {
        $results = $this->getQuery()->execute('en_US', 2, 1);
        $this->assertCount(2, $results);
        $codeForFirstResult = $results[0]->getCode();

        $results = $this->getQuery()->execute('en_US', 2, 2);
        $this->assertCount(2, $results);
        $this->assertNotEquals($codeForFirstResult, $results[0]->getCode());

        $results = $this->getQuery()->execute('en_US', 2, 200);
        $this->assertCount(0, $results);
    }

    public function test_it_uses_the_locale_code_for_labels(): void
    {
        $results = $this->getQuery()->execute('fr_FR', 100);
        $this->assertNotEmpty($results);

        $association = $this->findAssociationTypeInResults('X_SELL', $results);
        $this->assertNotNull($association);
        $this->assertSame('Vente croisée', $association->getLabel('fr_FR'));

        $association = $this->findAssociationTypeInResults('UPSELL', $results);
        $this->assertNotNull($association);
        $this->assertSame('Vente incitative', $association->getLabel('fr_FR'));

        $results = $this->getQuery()->execute('unknown', 100);
        $this->assertNotEmpty($results);

        $association = $this->findAssociationTypeInResults('X_SELL', $results);
        $this->assertNotNull($association);
        $this->assertSame('[X_SELL]', $association->getLabel('unknown'));
    }

    /**
     * @param AssociationType[] $results
     */
    private function findAssociationTypeInResults(string $associationTypeCode, array $results): ?AssociationType
    {
        foreach ($results as $result) {
            if ($associationTypeCode === $result->getCode()) {
                return $result;
            }
        }

        return null;
    }

    private function getQuery(): SqlFindAssociationTypes
    {
        return $this->get('akeneo.pim.structure.query.find_association_types');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
