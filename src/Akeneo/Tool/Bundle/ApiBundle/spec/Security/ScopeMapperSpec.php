<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\ApiBundle\Security;

use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapper;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeMapperSpec extends ObjectBehavior
{
    function it_is_instantiable()
    {
        $this->shouldHaveType(ScopeMapper::class);
    }

    public function it_returns_the_list_of_all_available_scopes()
    {
        $this->getAllScopes()->shouldReturn([
            'read_catalog_structure',
            'write_catalog_structure',
            'read_attribute_options',
            'write_attribute_options',
            'read_categories',
            'write_categories',
            'read_channel_localization',
            'read_channel_settings',
            'write_channel_settings',
            'read_association_types',
            'write_association_types',
            'read_products',
            'write_products',
            'delete_products',
        ]);
    }

    public function it_throws_on_unknown_scope()
    {
        $this->shouldThrow(\LogicException::class)->during('getAcls', ['unknown_scope']);
    }

    public function it_returns_the_acls_for_the_scope_read_catalog_structure()
    {
        $this->getAcls('read_catalog_structure')->shouldReturn([
            'pim_api_attribute_group_list',
            'pim_api_attribute_list',
            'pim_api_family_list',
            'pim_api_family_variant_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_catalog_structure()
    {
        $this->getAcls('write_catalog_structure')->shouldReturn([
            'pim_api_attribute_edit',
            'pim_api_attribute_group_edit',
            'pim_api_attribute_group_list',
            'pim_api_attribute_list',
            'pim_api_family_edit',
            'pim_api_family_list',
            'pim_api_family_variant_edit',
            'pim_api_family_variant_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_read_attribute_options()
    {
        $this->getAcls('read_attribute_options')->shouldReturn([
            'pim_api_attribute_option_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_attribute_options()
    {
        $this->getAcls('write_attribute_options')->shouldReturn([
            'pim_api_attribute_option_edit',
            'pim_api_attribute_option_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_read_categories()
    {
        $this->getAcls('read_categories')->shouldReturn([
            'pim_api_category_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_categories()
    {
        $this->getAcls('write_categories')->shouldReturn([
            'pim_api_category_edit',
            'pim_api_category_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_read_channel_localization()
    {
        $this->getAcls('read_channel_localization')->shouldReturn([
            'pim_api_currency_list',
            'pim_api_locale_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_read_channel_settings()
    {
        $this->getAcls('read_channel_settings')->shouldReturn([
            'pim_api_channel_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_channel_settings()
    {
        $this->getAcls('write_channel_settings')->shouldReturn([
            'pim_api_channel_edit',
            'pim_api_channel_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_read_association_types()
    {
        $this->getAcls('read_association_types')->shouldReturn([
            'pim_api_association_type_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_association_types()
    {
        $this->getAcls('write_association_types')->shouldReturn([
            'pim_api_association_type_edit',
            'pim_api_association_type_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_products()
    {
        $this->getAcls('write_products')->shouldReturn([
            'pim_api_product_edit',
            'pim_api_product_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_read_products()
    {
        $this->getAcls('read_products')->shouldReturn([
            'pim_api_product_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_delete_products()
    {
        $this->getAcls('delete_products')->shouldReturn([
            'pim_api_product_edit',
            'pim_api_product_list',
            'pim_api_product_remove',
        ]);
    }

    public function it_filter_scopes_by_removing_scopes_inherited_from_the_hierarchy()
    {
        $this->formalizeScopes([
            'read_products',
            'write_products',
        ])->shouldReturn([
            'write_products',
        ]);
    }

    public function it_filter_scopes_by_recursiverly_removing_scopes_inherited_from_the_hierarchy()
    {
        $this->formalizeScopes([
            'read_products',
            'delete_products',
        ])->shouldReturn([
            'delete_products',
        ]);
    }

    public function it_automatically_add_read_channel_localization_when_read_channel_settings_is_present()
    {
        $this->formalizeScopes([
            'read_channel_settings',
        ])->shouldReturn([
            'read_channel_localization',
            'read_channel_settings',
        ]);
    }

    public function it_automatically_add_read_channel_localization_when_write_channel_settings_is_present()
    {
        $this->formalizeScopes([
            'write_channel_settings',
        ])->shouldReturn([
            'read_channel_localization',
            'write_channel_settings',
        ]);
    }
}
