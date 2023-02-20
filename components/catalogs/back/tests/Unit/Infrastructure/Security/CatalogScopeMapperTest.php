<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Security;

use Akeneo\Catalogs\Infrastructure\Security\CatalogScopeMapper;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogScopeMapperTest extends TestCase
{
    private ?FeatureFlags $featureFlags;
    private bool $featureFlagIsEnabled = true;
    private ?CatalogScopeMapper $mapper;

    protected function setUp(): void
    {
        $this->featureFlagIsEnabled = true;
        $this->featureFlags = $this->createMock(FeatureFlags::class);
        $this->featureFlags->method('isEnabled')->will($this->returnCallback(fn (): bool => $this->featureFlagIsEnabled));

        $this->mapper = new CatalogScopeMapper($this->featureFlags);
    }

    public function testItReturnsNothingWhenTheFeatureIsDisabled(): void
    {
        $this->featureFlagIsEnabled = false;

        $this->assertEquals([], $this->mapper->getScopes());
    }

    public function testItReturnsScopes(): void
    {
        $this->assertEquals([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ], $this->mapper->getScopes());
    }

    /**
     * @dataProvider acls
     */
    public function testItReturnsAclsForOneScope(string $scope, array $expected): void
    {
        $this->assertEquals($expected, $this->mapper->getAcls($scope));
    }

    public function acls(): array
    {
        return [
            'read' => [
                'scope' => 'read_catalogs',
                'result' => ['pim_api_catalog_list'],
            ],
            'write' => [
                'scope' => 'write_catalogs',
                'result' => ['pim_api_catalog_edit'],
            ],
            'delete' => [
                'scope' => 'delete_catalogs',
                'result' => ['pim_api_catalog_remove'],
            ],
            'unknown' => [
                'scope' => 'unknown',
                'result' => [],
            ],
        ];
    }

    /**
     * @dataProvider messages
     */
    public function testItReturnsMessagesForOneScope(string $scope, ?array $expected): void
    {
        $this->assertEquals($expected, $this->mapper->getMessage($scope));
    }

    public function messages(): array
    {
        return [
            'read' => [
                'scope' => 'read_catalogs',
                'result' => [
                    'icon' => 'catalogs',
                    'type' => 'view',
                    'entities' => 'catalogs',
                ],
            ],
            'write' => [
                'scope' => 'write_catalogs',
                'result' => [
                    'icon' => 'catalogs',
                    'type' => 'edit',
                    'entities' => 'catalogs',
                ],
            ],
            'delete' => [
                'scope' => 'delete_catalogs',
                'result' => [
                    'icon' => 'catalogs',
                    'type' => 'delete',
                    'entities' => 'catalogs',
                ],
            ],
            'unknown' => [
                'scope' => 'unknown',
                'result' => null,
            ],
        ];
    }

    /**
     * @dataProvider hierarchy
     */
    public function testItReturnsLowerHierarchyScopesForOneScope(string $scope, array $expected): void
    {
        $this->assertEquals($expected, $this->mapper->getLowerHierarchyScopes($scope));
    }

    public function hierarchy(): array
    {
        return [
            'read' => [
                'scope' => 'read_catalogs',
                'result' => [],
            ],
            'write' => [
                'scope' => 'write_catalogs',
                'result' => [
                    'read_catalogs',
                ],
            ],
            'delete' => [
                'scope' => 'delete_catalogs',
                'result' => [
                    'read_catalogs',
                    'write_catalogs',
                ],
            ],
            'unknown' => [
                'scope' => 'unknown',
                'result' => [],
            ],
        ];
    }
}
