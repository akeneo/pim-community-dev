<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Syndication\Infrastructure\Query\InMemoryFindSystemSources;
use Akeneo\Platform\Syndication\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class InMemoryFindSystemSourcesIntegrationTest extends ControllerIntegrationTestCase
{
    public function test_it_returns_association_types_depending_on_search(): void
    {
        $results = $this->getQuery()->execute('en_US', 100, 0, null, 'string');

        $this->assertNotNull($this->findSystemFieldInResults('family', $results));
        $this->assertNotNull($this->findSystemFieldInResults('family_variant', $results));
        $this->assertNotNull($this->findSystemFieldInResults('categories', $results));
        $this->assertNotNull($this->findSystemFieldInResults('enabled', $results));

        $results = $this->getQuery()->execute('en_US', 100, 0, 'family', 'string');

        $this->assertNotNull($this->findSystemFieldInResults('family', $results));
        $this->assertNotNull($this->findSystemFieldInResults('family_variant', $results));
        $this->assertNull($this->findSystemFieldInResults('categories', $results));
        $this->assertNull($this->findSystemFieldInResults('enabled', $results));

        $results = $this->getQuery()->execute('fr_FR', 100, 0, 'Famille', 'string');

        $this->assertNotNull($this->findSystemFieldInResults('family', $results));
        $this->assertNotNull($this->findSystemFieldInResults('family_variant', $results));
        $this->assertNull($this->findSystemFieldInResults('categories', $results));
        $this->assertNull($this->findSystemFieldInResults('enabled', $results));
    }

    public function test_it_returns_paginate_results(): void
    {
        $results = $this->getQuery()->execute('en_US', 2, 1, null, 'string');
        $this->assertCount(2, $results);
        $codeForFirstResult = $results[0];

        $results = $this->getQuery()->execute('en_US', 2, 2, null, 'string');
        $this->assertCount(2, $results);
        $this->assertNotEquals($codeForFirstResult, $results[0]);

        $results = $this->getQuery()->execute('en_US', 2, 200, null, 'string');
        $this->assertCount(0, $results);
    }

    public function test_it_uses_the_locale_code_for_labels(): void
    {
        $results = $this->getQuery()->execute('fr_FR', 100, 100, null, 'string');
        $this->assertNotEmpty($results);
        $systemField = $this->findSystemFieldInResults('family_variant', $results);
        $this->assertNotNull($systemField);
        $systemField = $this->findSystemFieldInResults('groups', $results);
        $this->assertNotNull($systemField);

        $results = $this->getQuery()->execute('unknown', 100, 100, null, 'string');
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

    private function getQuery(): InMemoryFindSystemSources
    {
        return $this->get('akeneo.syndication.query.find_product_system_sources');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
