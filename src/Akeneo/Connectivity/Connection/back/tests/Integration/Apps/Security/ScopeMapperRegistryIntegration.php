<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeMapperRegistryIntegration extends TestCase
{
    private ScopeMapperRegistry $scopeMapperRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeMapperRegistry = $this->get(ScopeMapperRegistry::class);
    }

    protected function getConfiguration(): ?Configuration
    {
        return null;
    }

    /**
     * @group ce
     *
     * /!\ warning /!\
     *
     * If you are wondering why we have this test in there with hardcoded values,
     * it's because we want to be informed when Scopes or ACLs are modified in other features.
     * It has impacts on the Apps feature that are not obvious.
     * Please contact the team in charge to know more.
     *
     * /!\ warning /!\
     */
    public function test_it_retrieves_all_authorization_scopes(): void
    {
        $scopes = $this->scopeMapperRegistry->getAllScopes();
        \sort($scopes);

        $this->assertEquals([
            'delete_products',
            'read_association_types',
            'read_attribute_options',
            'read_catalog_structure',
            'read_categories',
            'read_channel_localization',
            'read_channel_settings',
            'read_products',
            'write_association_types',
            'write_attribute_options',
            'write_catalog_structure',
            'write_categories',
            'write_channel_settings',
            'write_products',
        ], $scopes);
    }

    /**
     * @group ce
     *
     * /!\ warning /!\
     *
     * If you are wondering why we have this test in there with hardcoded values,
     * it's because we want to be informed when Scopes or ACLs are modified in other features.
     * It has impacts on the Apps feature that are not obvious.
     * Please contact the team in charge to know more.
     *
     * /!\ warning /!\
     */
    public function test_it_retrieves_all_acls(): void
    {
        $expected = [
            'read_channel_localization' => [
                'pim_api_locale_list',
                'pim_api_currency_list',
            ],
            'read_channel_settings' => [
                'pim_api_channel_list',
            ],
            'write_channel_settings' => [
                'pim_api_channel_edit',
                'pim_api_channel_list',
            ],
            'read_products' => [
                'pim_api_product_list',
            ],
            'write_products' => [
                'pim_api_product_edit',
                'pim_api_product_list',
            ],
            'delete_products' => [
                'pim_api_product_remove',
                'pim_api_product_list',
                'pim_api_product_edit',
            ],
            'read_categories' => [
                'pim_api_category_list',
            ],
            'write_categories' => [
                'pim_api_category_edit',
                'pim_api_category_list',
            ],
            'read_catalog_structure' => [
                'pim_api_attribute_list',
                'pim_api_attribute_group_list',
                'pim_api_family_list',
                'pim_api_family_variant_list',
            ],
            'write_catalog_structure' => [
                'pim_api_attribute_edit',
                'pim_api_attribute_group_edit',
                'pim_api_family_edit',
                'pim_api_family_variant_edit',
                'pim_api_attribute_list',
                'pim_api_attribute_group_list',
                'pim_api_family_list',
                'pim_api_family_variant_list',
            ],
            'read_attribute_options' => [
                'pim_api_attribute_option_list',
            ],
            'write_attribute_options' => [
                'pim_api_attribute_option_edit',
                'pim_api_attribute_option_list',
            ],
            'read_association_types' => [
                'pim_api_association_type_list',
            ],
            'write_association_types' => [
                'pim_api_association_type_edit',
                'pim_api_association_type_list',
            ],
            'read_catalogs' => [
                'pim_api_catalog_list',
            ],
            'write_catalogs' => [
                'pim_api_catalog_edit',
                'pim_api_catalog_list',
            ],
        ];

        $scopes = $this->scopeMapperRegistry->getAllScopes();

        foreach ($scopes as $scope) {
            $acls = $this->scopeMapperRegistry->getAcls([$scope]);
            $this->assertEquals($expected[$scope], $acls);
        }
    }
}
