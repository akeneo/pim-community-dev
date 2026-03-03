<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Structure\Component\Security\CatalogStructureScopeMapper;
use PhpSpec\ObjectBehavior;

class CatalogStructureScopeMapperSpec extends ObjectBehavior
{
    public function it_is_a_catalog_structure_scope_mapper(): void
    {
        $this->shouldHaveType(CatalogStructureScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_scopes(): void
    {
        $this->getScopes()->shouldReturn([
            'read_catalog_structure',
            'write_catalog_structure',
        ]);
    }

    public function it_provides_acls_that_corresponds_to_the_read_catalog_structure_scope(): void
    {
        $this->getAcls('read_catalog_structure')->shouldReturn([
            'pim_api_attribute_list',
            'pim_api_attribute_group_list',
            'pim_api_family_list',
            'pim_api_family_variant_list',
        ]);
    }

    public function it_provides_acls_that_corresponds_to_the_write_catalog_structure_scope(): void
    {
        $this->getAcls('write_catalog_structure')->shouldReturn([
            'pim_api_attribute_edit',
            'pim_api_attribute_group_edit',
            'pim_api_family_edit',
            'pim_api_family_variant_edit',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getAcls', ['unknown_scope']);
    }

    public function it_provides_message_that_corresponds_to_read_catalog_structure_scope(): void
    {
        $this->getMessage('read_catalog_structure')->shouldReturn([
            'icon' => 'catalog_structure',
            'type' => 'view',
            'entities' => 'catalog_structure',
        ]);
    }

    public function it_provides_message_that_corresponds_to_write_catalog_structure_scope(): void
    {
        $this->getMessage('write_catalog_structure')->shouldReturn([
            'icon' => 'catalog_structure',
            'type' => 'edit',
            'entities' => 'catalog_structure',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getMessage', ['unknown_scope']);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_catalog_structure_scope(): void
    {
        $this->getLowerHierarchyScopes('read_catalog_structure')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_catalog_structure_scope(): void
    {
        $this->getLowerHierarchyScopes('write_catalog_structure')->shouldReturn([
            'read_catalog_structure',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getLowerHierarchyScopes', ['unknown_scope']);
    }
}
