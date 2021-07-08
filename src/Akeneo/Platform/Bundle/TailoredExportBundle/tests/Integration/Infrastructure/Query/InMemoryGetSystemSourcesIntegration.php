<?php

namespace Akeneo\Pim\TailoredExport\Test\Integration\Infrastructure\Query;

use Akeneo\Pim\TailoredExport\Infrastructure\Query\InMemoryGetSystemSources;
use Akeneo\Pim\TailoredExport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class InMemoryGetSystemSourcesIntegration extends ControllerIntegrationTestCase
{
    public function test_it_returns_association_types_depending_on_search(): void
    {
        $results = $this->getSqlGetSystemSources()->execute('en_US', 100);

        $this->assertNotNull($this->findSystemFieldInResults('family', $results));
        $this->assertNotNull($this->findSystemFieldInResults('family_variant', $results));
        $this->assertNotNull($this->findSystemFieldInResults('categories', $results));
        $this->assertNotNull($this->findSystemFieldInResults('enabled', $results));

        $results = $this->getSqlGetSystemSources()->execute('en_US', 100, 0, 'family');

        $this->assertNotNull($this->findSystemFieldInResults('family', $results));
        $this->assertNotNull($this->findSystemFieldInResults('family_variant', $results));
        $this->assertNull($this->findSystemFieldInResults('categories', $results));
        $this->assertNull($this->findSystemFieldInResults('enabled', $results));

        $results = $this->getSqlGetSystemSources()->execute('fr_FR', 100, 0, 'Famille');

        $this->assertNotNull($this->findSystemFieldInResults('family', $results));
        $this->assertNotNull($this->findSystemFieldInResults('family_variant', $results));
        $this->assertNull($this->findSystemFieldInResults('categories', $results));
        $this->assertNull($this->findSystemFieldInResults('enabled', $results));
    }

    public function test_it_returns_paginate_results(): void
    {
        $results = $this->getSqlGetSystemSources()->execute('en_US', 2, 1);
        $this->assertCount(2, $results);
        $codeForFirstResult = $results[0];

        $results = $this->getSqlGetSystemSources()->execute('en_US', 2, 2);
        $this->assertCount(2, $results);
        $this->assertNotEquals($codeForFirstResult, $results[0]);

        $results = $this->getSqlGetSystemSources()->execute('en_US', 2, 200);
        $this->assertCount(0, $results);
    }

    public function test_it_uses_the_locale_code_for_labels(): void
    {
        $results = $this->getSqlGetSystemSources()->execute('fr_FR', 100);
        $this->assertNotEmpty($results);
        $systemField = $this->findSystemFieldInResults('family_variant', $results);
        $this->assertNotNull($systemField);
        $systemField = $this->findSystemFieldInResults('groups', $results);
        $this->assertNotNull($systemField);

        $results = $this->getSqlGetSystemSources()->execute('unknown', 100);
        $this->assertNotNull($results);
        $systemField = $this->findSystemFieldInResults('family_variant', $results);
        $this->assertNotNull($systemField);
    }

    private function findSystemFieldInResults(string $systemField, array $results): ?string
    {
        foreach ($results as $result) {
            if ($systemField === $result) {
                return $result;
            }
        }

        return null;
    }

    private function getSqlGetSystemSources(): InMemoryGetSystemSources
    {
        return $this->get('pimee_tailored_export.query.sql.get_system_sources');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
