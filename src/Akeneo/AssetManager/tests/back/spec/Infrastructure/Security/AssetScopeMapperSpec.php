<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Security;

use Akeneo\AssetManager\Infrastructure\Security\AssetScopeMapper;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use PhpSpec\ObjectBehavior;

class AssetScopeMapperSpec extends ObjectBehavior
{
    public function it_is_a_asset_scope_mapper(): void
    {
        $this->shouldHaveType(AssetScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getScopes()->shouldReturn([
            'write_asset_families',
            'read_asset_families',
            'write_assets',
            'read_assets',
            'delete_assets',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_asset_families_scope(): void
    {
        $this->getAcls('write_asset_families')->shouldReturn([
            'pim_api_asset_family_edit',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_asset_families_scope(): void
    {
        $this->getAcls('read_asset_families')->shouldReturn([
            'pim_api_asset_family_list',
        ]);
    }


    public function it_provides_acls_that_correspond_to_the_write_assets_scope(): void
    {
        $this->getAcls('write_assets')->shouldReturn([
            'pim_api_asset_edit',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_assets_scope(): void
    {
        $this->getAcls('read_assets')->shouldReturn([
            'pim_api_asset_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_delete_assets_scope(): void
    {
        $this->getAcls('delete_assets')->shouldReturn([
            'pim_api_asset_remove',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_message_that_corresponds_to_the_write_asset_families_scope(): void
    {
        $this->getMessage('write_asset_families')->shouldReturn([
            'icon' => 'asset_families',
            'type' => 'edit',
            'entities' => 'asset_families',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_read_asset_families_scope(): void
    {
        $this->getMessage('read_asset_families')->shouldReturn([
            'icon' => 'asset_families',
            'type' => 'view',
            'entities' => 'asset_families',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_assets_scope(): void
    {
        $this->getMessage('write_assets')->shouldReturn([
            'icon' => 'assets',
            'type' => 'edit',
            'entities' => 'assets',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_read_assets_scope(): void
    {
        $this->getMessage('read_assets')->shouldReturn([
            'icon' => 'assets',
            'type' => 'view',
            'entities' => 'assets',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_delete_assets_scope(): void
    {
        $this->getMessage('delete_assets')->shouldReturn([
            'icon' => 'assets',
            'type' => 'delete',
            'entities' => 'assets',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->getMessage('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_asset_families_scope(): void
    {
        $this->getLowerHierarchyScopes('read_asset_families')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_assets_scope(): void
    {
        $this->getLowerHierarchyScopes('read_assets')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_asset_families_scope(): void
    {
        $this->getLowerHierarchyScopes('write_asset_families')->shouldReturn([
            'read_asset_families',
        ]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_assets_scope(): void
    {
        $this->getLowerHierarchyScopes('write_assets')->shouldReturn([
            'read_assets',
        ]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_delete_assets_scope(): void
    {
        $this->getLowerHierarchyScopes('delete_assets')->shouldReturn([
            'write_assets',
            'read_assets',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->getLowerHierarchyScopes('unknown_scope')->shouldReturn([]);
    }
}
