<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Enrichment\Component\Security\CategoriesScopeMapper;
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
        $this->getScopes()->shouldReturn([
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

    public function it_throws_an_exception_when_trying_to_get_acls_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getAcls', ['unknown_scope']);
    }

    public function it_provides_message_that_corresponds_to_the_read_categories_scope(): void
    {
        $this->getMessage('read_categories')->shouldReturn([
            'icon' => 'categories',
            'type' => 'view',
            'entities' => 'categories',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_categories_scope(): void
    {
        $this->getMessage('write_categories')->shouldReturn([
            'icon' => 'categories',
            'type' => 'edit',
            'entities' => 'categories',
        ]);
    }

    public function it_throws_an_exception_when_trying_to_get_message_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getMessage', ['unknown_scope']);
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

    public function it_throws_an_exception_when_trying_to_get_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getLowerHierarchyScopes', ['unknown_scope']);
    }
}
