<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\TailoredImport\Infrastructure\Query\InMemoryFindSystemTargets;
use Akeneo\Platform\TailoredImport\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class InMemoryFindSystemTargetsIntegrationTest extends IntegrationTestCase
{
    public function test_it_returns_association_types_depending_on_search(): void
    {
        $results = $this->getQuery()->execute('en_US', 100);

        $this->assertNotNull($this->findSystemTargetInResults('family', $results));
        $this->assertNotNull($this->findSystemTargetInResults('family_variant', $results));
        $this->assertNotNull($this->findSystemTargetInResults('categories', $results));
        $this->assertNotNull($this->findSystemTargetInResults('enabled', $results));

        $results = $this->getQuery()->execute('en_US', 100, 0, 'family');

        $this->assertNotNull($this->findSystemTargetInResults('family', $results));
        $this->assertNotNull($this->findSystemTargetInResults('family_variant', $results));
        $this->assertNull($this->findSystemTargetInResults('categories', $results));
        $this->assertNull($this->findSystemTargetInResults('enabled', $results));

        $results = $this->getQuery()->execute('fr_FR', 100, 0, 'Famille');

        $this->assertNotNull($this->findSystemTargetInResults('family', $results));
        $this->assertNotNull($this->findSystemTargetInResults('family_variant', $results));
        $this->assertNull($this->findSystemTargetInResults('categories', $results));
        $this->assertNull($this->findSystemTargetInResults('enabled', $results));
    }

    public function test_it_returns_paginate_results(): void
    {
        $results = $this->getQuery()->execute('en_US', 2, 1);
        $this->assertCount(2, $results);
        $codeForFirstResult = $results[0];

        $results = $this->getQuery()->execute('en_US', 2, 2);
        $this->assertCount(2, $results);
        $this->assertNotEquals($codeForFirstResult, $results[0]);

        $results = $this->getQuery()->execute('en_US', 2, 200);
        $this->assertCount(0, $results);
    }

    public function test_it_uses_the_locale_code_for_labels(): void
    {
        $results = $this->getQuery()->execute('fr_FR', 100);
        $this->assertNotEmpty($results);
        $systemTarget = $this->findSystemTargetInResults('family_variant', $results);
        $this->assertNotNull($systemTarget);

        $results = $this->getQuery()->execute('unknown', 100);
        $this->assertNotNull($results);
        $systemTarget = $this->findSystemTargetInResults('family_variant', $results);
        $this->assertNotNull($systemTarget);
    }

    private function findSystemTargetInResults(string $systemTarget, array $results): ?string
    {
        foreach ($results as $result) {
            if ($systemTarget === $result) {
                return $result;
            }
        }

        return null;
    }

    private function getQuery(): InMemoryFindSystemTargets
    {
        return $this->get('akeneo.tailored_import.query.find_system_targets');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
