<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Security;

use Akeneo\Pim\Enrichment\Component\Security\CategoriesScopeMapper;
use Akeneo\Tool\Component\Api\Security\ScopeMapperInterface;
use PhpSpec\ObjectBehavior;

class CategoriesScopeMapperSpec extends ObjectBehavior
{
    public function it_is_a_categories_scope_mapper(): void
    {
        $this->shouldHaveType(CategoriesScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getAllScopes()->shouldReturn([
            'read_categories',
            'write_categories',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_categories_scope(): void
    {
        $this->getAcls('read_categories')->shouldReturn([
            'pim_api_category_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_categories_scope(): void
    {
        $this->getAcls('write_categories')->shouldReturn([
            'pim_api_category_edit',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_message_that_correspond_to_read_categories_scope(): void
    {
        $this->getMessage('read_categories')->shouldReturn([
            'icon' => 'categories',
            'type' => 'view',
            'entities' => 'categories',
        ]);
    }

    public function it_provides_message_that_correspond_to_write_categories_scope(): void
    {
        $this->getMessage('write_categories')->shouldReturn([
            'icon' => 'categories',
            'type' => 'edit',
            'entities' => 'categories',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->getMessage('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_categories_scope(): void
    {
        $this->getLowerHierarchyScopes('read_categories')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_categories_scope(): void
    {
        $this->getLowerHierarchyScopes('write_categories')->shouldReturn([
            'read_categories',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->getLowerHierarchyScopes('unknown_scope')->shouldReturn([]);
    }
}
