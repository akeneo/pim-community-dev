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

    public function it_returns_an_empty_message_list_on_empty_scope_string()
    {
        $this->getMessages([])->shouldReturn([]);
    }

    public function it_throws_an_exception_on_unknown_scope()
    {
        $this->shouldThrow(\LogicException::class)->during('getMessages', [['unknown_scope']]);
    }

    public function it_returns_a_message_list_on_complete_scope_list()
    {
        $scopes = [
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
        ];

        $this->getMessages($scopes)->shouldReturn([
            ['icon' => 'products', 'type' => 'delete', 'entities' => 'products'],
            ['icon' => 'channel_localization', 'type' => 'view', 'entities' => 'channel_localization'],
            ['icon' => 'association_types', 'type' => 'edit', 'entities' => 'association_types'],
            ['icon' => 'attribute_options', 'type' => 'edit', 'entities' => 'attribute_options'],
            ['icon' => 'catalog_structure', 'type' => 'edit', 'entities' => 'catalog_structure'],
            ['icon' => 'categories', 'type' => 'edit', 'entities' => 'categories'],
            ['icon' => 'channel_settings', 'type' => 'edit', 'entities' => 'channel_settings'],
        ]);
    }

    public function it_returns_a_view_messages_on_read_scope_list()
    {
        $scopes = [
            'read_catalog_structure',
            'read_attribute_options',
            'read_categories',
            'read_channel_localization',
            'read_channel_settings',
            'read_association_types',
            'read_products',
        ];

        $this->getMessages($scopes)->shouldReturn([
            ['icon' => 'association_types', 'type' => 'view', 'entities' => 'association_types'],
            ['icon' => 'attribute_options', 'type' => 'view', 'entities' => 'attribute_options'],
            ['icon' => 'catalog_structure', 'type' => 'view', 'entities' => 'catalog_structure'],
            ['icon' => 'categories', 'type' => 'view', 'entities' => 'categories'],
            ['icon' => 'channel_localization', 'type' => 'view', 'entities' => 'channel_localization'],
            ['icon' => 'channel_settings', 'type' => 'view', 'entities' => 'channel_settings'],
            ['icon' => 'products', 'type' => 'view', 'entities' => 'products'],
        ]);
    }

    public function it_returns_a_edit_messages_on_write_scope_list()
    {
        $scopes = [
            'write_catalog_structure',
            'write_attribute_options',
            'write_categories',
            'write_channel_settings',
            'write_association_types',
            'write_products',
        ];

        $this->getMessages($scopes)->shouldReturn([
            ['icon' => 'association_types', 'type' => 'edit', 'entities' => 'association_types'],
            ['icon' => 'attribute_options', 'type' => 'edit', 'entities' => 'attribute_options'],
            ['icon' => 'catalog_structure', 'type' => 'edit', 'entities' => 'catalog_structure'],
            ['icon' => 'categories', 'type' => 'edit', 'entities' => 'categories'],
            ['icon' => 'channel_settings', 'type' => 'edit', 'entities' => 'channel_settings'],
            ['icon' => 'products', 'type' => 'edit', 'entities' => 'products'],
        ]);
    }

    public function it_returns_a_delete_messages_on_delete_scope_list()
    {
        $scopes = [
            'delete_products',
        ];

        $this->getMessages($scopes) ->shouldReturn([
            ['icon' => 'products', 'type' => 'delete', 'entities' => 'products'],
        ]);
    }

    public function it_returns_a_products_messages_on_product_scope_list_and_respects_the_hierarchy()
    {
        $this->getMessages(['read_products'])->shouldReturn([
            ['icon' => 'products', 'type' => 'view', 'entities' => 'products'],
        ]);

        $this->getMessages(['read_products','write_products'])->shouldReturn([
            ['icon' => 'products', 'type' => 'edit', 'entities' => 'products'],
        ]);

        $this->getMessages(['read_products','write_products', 'delete_products'])->shouldReturn([
            ['icon' => 'products', 'type' => 'delete', 'entities' => 'products'],
        ]);
    }

    public function it_returns_a_catalog_structure_messages_on_catalog_structure_scope_list_and_respects_the_hierarchy()
    {
        $this->getMessages(['read_catalog_structure'])->shouldReturn([
            ['icon' => 'catalog_structure', 'type' => 'view', 'entities' => 'catalog_structure'],
        ]);

        $this->getMessages(['read_catalog_structure','write_catalog_structure'])->shouldReturn([
            ['icon' => 'catalog_structure', 'type' => 'edit', 'entities' => 'catalog_structure'],
        ]);
    }

    public function it_returns_a_attribute_options_messages_on_catalog_attribute_options_list_and_respects_the_hierarchy()
    {
        $this->getMessages(['read_attribute_options'])->shouldReturn([
            ['icon' => 'attribute_options', 'type' => 'view', 'entities' => 'attribute_options'],
        ]);

        $this->getMessages(['read_attribute_options','write_attribute_options'])->shouldReturn([
            ['icon' => 'attribute_options', 'type' => 'edit', 'entities' => 'attribute_options'],
        ]);
    }

    public function it_returns_a_association_types_messages_on_catalog_association_types_list_and_respects_the_hierarchy()
    {
        $this->getMessages(['read_association_types'])->shouldReturn([
            ['icon' => 'association_types', 'type' => 'view', 'entities' => 'association_types'],
        ]);

        $this->getMessages(['read_association_types','write_association_types'])->shouldReturn([
            ['icon' => 'association_types', 'type' => 'edit', 'entities' => 'association_types'],
        ]);
    }

    public function it_returns_a_category_messages_on_catalog_category_list_and_respects_the_hierarchy()
    {
        $this->getMessages(['read_categories'])->shouldReturn([
            ['icon' => 'categories', 'type' => 'view', 'entities' => 'categories'],
        ]);

        $this->getMessages(['read_categories','write_categories'])->shouldReturn([
            ['icon' => 'categories', 'type' => 'edit', 'entities' => 'categories'],
        ]);
    }

    public function it_returns_a_channel_settings_messages_on_catalog_channel_settings_list_and_respects_the_hierarchy()
    {
        $this->getMessages(['read_channel_settings'])->shouldReturn([
            ['icon' => 'channel_settings', 'type' => 'view', 'entities' => 'channel_settings'],
        ]);

        $this->getMessages(['read_channel_settings','write_channel_settings'])->shouldReturn([
            ['icon' => 'channel_settings', 'type' => 'edit', 'entities' => 'channel_settings'],
        ]);
    }
}
