<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\ScopeListComparator;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeListComparatorIntegration extends TestCase
{
    private ScopeListComparator $scopeListComparator;
    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeListComparator = $this->get(ScopeListComparator::class);
    }

    protected function getConfiguration(): ?Configuration
    {
        return null;
    }

    public function test_it_returns_new_scopes(): void
    {
        $originalScopes = [
            'write_products',
            'read_categories'
        ];

        $requestedScopes = [
            ...$originalScopes,
            'write_association_types'
        ];

        $expected = [
            'read_association_types',
            'write_association_types'
        ];

        $newScopes = $this->scopeListComparator->diff($requestedScopes, $originalScopes);

        $this->assertEquals($newScopes, $expected);
    }

    public function test_it_returns_empty_scopes(): void
    {
        $originalScopes = [
            'write_products',
            'read_categories'
        ];

        $requestedScopes = [...$originalScopes];

        $expected = [];

        $newScopes = $this->scopeListComparator->diff($requestedScopes, $originalScopes);

        $this->assertEquals($newScopes, $expected);
    }

    public function test_it_returns_a_new_scope_with_hightest_level(): void
    {
        $originalScopes = ['read_categories'];

        $requestedScopes = ['write_categories'];

        $expected = ['write_categories'];

        $newScopes = $this->scopeListComparator->diff($requestedScopes, $originalScopes);

        $this->assertEquals($newScopes, $expected);
    }

    public function test_it_returns_empty_scopes_when_lower_lever_scopes_are_requested(): void
    {
        $originalScopes = [
            'write_association_types',
            'write_attribute_options',
            'write_catalog_structure',
        ];

        $requestedScopes = [
            'read_association_types',
            'read_attribute_options',
            'read_catalog_structure',
        ];

        $expected = [];

        $newScopes = $this->scopeListComparator->diff($requestedScopes, $originalScopes);

        $this->assertEquals($newScopes, $expected);
    }
}
