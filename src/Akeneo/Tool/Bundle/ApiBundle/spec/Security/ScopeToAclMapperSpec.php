<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\ApiBundle\Security;

use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeToAclMapper;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeToAclMapperSpec extends ObjectBehavior
{
    function it_is_instantiable()
    {
        $this->shouldHaveType(ScopeToAclMapper::class);
    }

    public function it_returns_list_of_all_available_scopes()
    {
        $this->getAllScopes()->shouldReturn([
            ScopeToAclMapper::SCOPE_READ_CATALOG_STRUCTURE,
            ScopeToAclMapper::SCOPE_WRITE_CATALOG_STRUCTURE,
            ScopeToAclMapper::SCOPE_READ_EXTENDED_PRODUCT_DATA,
            ScopeToAclMapper::SCOPE_WRITE_EXTENDED_PRODUCT_DATA,
            ScopeToAclMapper::SCOPE_READ_TARGET_SETTINGS,
            ScopeToAclMapper::SCOPE_WRITE_TARGET_SETTINGS,
            ScopeToAclMapper::SCOPE_READ_ASSOCIATION_TYPES,
            ScopeToAclMapper::SCOPE_WRITE_ASSOCIATION_TYPES,
            ScopeToAclMapper::SCOPE_WRITE_PRODUCTS,
            ScopeToAclMapper::SCOPE_READ_PRODUCTS,
            ScopeToAclMapper::SCOPE_DELETE_PRODUCTS,
        ]);
    }

    public function it_returns_empty_list_on_unknown_scope()
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_returns_access_catalog_structure_acl_list_on_read_catalog_structure_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_READ_CATALOG_STRUCTURE)->shouldReturn([
            'pim_api_attribute_list',
            'pim_api_attribute_group_list',
            'pim_api_family_list',
            'pim_api_family_variant_list',
        ]);
    }

    public function it_returns_access_and_edit_catalog_structure_acl_list_on_write_catalog_structure_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_WRITE_CATALOG_STRUCTURE)->shouldReturn([
            'pim_api_attribute_list',
            'pim_api_attribute_edit',
            'pim_api_attribute_group_list',
            'pim_api_attribute_group_edit',
            'pim_api_family_list',
            'pim_api_family_edit',
            'pim_api_family_variant_list',
            'pim_api_family_variant_edit',
        ]);
    }

    public function it_returns_access_extended_product_data_acl_list_on_read_extended_product_data_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_READ_EXTENDED_PRODUCT_DATA)->shouldReturn([
            'pim_api_attribute_option_list',
            'pim_api_category_list',
        ]);
    }

    public function it_returns_access_and_edit_extended_product_data_acl_list_on_write_extended_product_data_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_WRITE_EXTENDED_PRODUCT_DATA)->shouldReturn([
            'pim_api_attribute_option_list',
            'pim_api_attribute_option_edit',
            'pim_api_category_list',
            'pim_api_category_edit',
        ]);
    }

    public function it_returns_access_target_settings_acl_list_on_read_target_settings_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_READ_TARGET_SETTINGS)->shouldReturn([
            'pim_api_channel_list',
            'pim_api_locale_list',
            'pim_api_currency_list',
        ]);
    }

    public function it_returns_access_and_edit_target_settings_acl_list_on_write_target_settings_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_WRITE_TARGET_SETTINGS)->shouldReturn([
            'pim_api_channel_list',
            'pim_api_channel_edit',
            'pim_api_locale_list',
            'pim_api_currency_list',
        ]);
    }

    public function it_returns_access_association_types_acl_list_on_read_association_types_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_READ_ASSOCIATION_TYPES)->shouldReturn([
            'pim_api_association_type_list',
        ]);
    }

    public function it_returns_access_and_edit_association_types_acl_list_on_write_association_types_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_WRITE_ASSOCIATION_TYPES)->shouldReturn([
            'pim_api_association_type_list',
            'pim_api_association_type_edit',
        ]);
    }
    public function it_returns_access_product_acl_list_on_read_products_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_READ_PRODUCTS)->shouldReturn([
            'pim_api_product_list',
        ]);
    }

    public function it_returns_access_and_edit_product_acl_list_on_write_products_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_WRITE_PRODUCTS)->shouldReturn([
            'pim_api_product_list',
            'pim_api_product_edit',
        ]);
    }

    public function it_returns_remove_product_acl_list_on_delete_products_scope()
    {
        $this->getAcls(ScopeToAclMapper::SCOPE_DELETE_PRODUCTS)->shouldReturn([
            'pim_api_product_list',
            'pim_api_product_edit',
            'pim_api_product_remove',
        ]);
    }
}
