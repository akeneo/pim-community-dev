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

    public function it_returns_the_list_of_all_available_scopes()
    {
        $this->getAllScopes()->shouldReturn([
            'read_catalog_structure',
            'write_catalog_structure',
            'read_attribute_options',
            'write_attribute_options',
            'read_categories',
            'write_categories',
            'read_channel_settings',
            'write_channel_settings',
            'read_association_types',
            'write_association_types',
            'read_products',
            'write_products',
            'delete_products',
        ]);
    }

    public function it_throw_on_unknown_scope()
    {
        $this->shouldThrow(\LogicException::class)->during('getAcls', ['unknown_scope']);
    }

    public function it_returns_the_acls_for_the_scope_read_catalog_structure()
    {
        $this->getAcls('read_catalog_structure')->shouldReturn([
            'pim_api_attribute_list',
            'pim_api_attribute_group_list',
            'pim_api_family_list',
            'pim_api_family_variant_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_catalog_structure()
    {
        $this->getAcls('write_catalog_structure')->shouldReturn([
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

    public function it_returns_the_acls_for_the_scope_read_attribute_options()
    {
        $this->getAcls('read_attribute_options')->shouldReturn([
            'pim_api_attribute_option_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_attribute_options()
    {
        $this->getAcls('write_attribute_options')->shouldReturn([
            'pim_api_attribute_option_list',
            'pim_api_attribute_option_edit',
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
            'pim_api_category_list',
            'pim_api_category_edit',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_read_channel_settings()
    {
        $this->getAcls('read_channel_settings')->shouldReturn([
            'pim_api_channel_list',
            'pim_api_locale_list',
            'pim_api_currency_list',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_channel_settings()
    {
        $this->getAcls('write_channel_settings')->shouldReturn([
            'pim_api_channel_list',
            'pim_api_channel_edit',
            'pim_api_locale_list',
            'pim_api_currency_list',
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
            'pim_api_association_type_list',
            'pim_api_association_type_edit',
        ]);
    }

    public function it_returns_the_acls_for_the_scope_write_products()
    {
        $this->getAcls('write_products')->shouldReturn([
            'pim_api_product_list',
            'pim_api_product_edit',
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
            'pim_api_product_list',
            'pim_api_product_edit',
            'pim_api_product_remove',
        ]);
    }
}
