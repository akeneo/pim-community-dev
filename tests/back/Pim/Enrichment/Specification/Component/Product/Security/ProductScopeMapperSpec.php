<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Enrichment\Component\Product\Security\ProductScopeMapper;
use PhpSpec\ObjectBehavior;

class ProductScopeMapperSpec extends ObjectBehavior
{
    public function it_is_a_product_scope_mapper(): void
    {
        $this->shouldHaveType(ProductScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getScopes()->shouldReturn([
            'read_products',
            'write_products',
            'delete_products',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_product_scope(): void
    {
        $this->getAcls('read_products')->shouldReturn([
            'pim_api_product_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_product_scope(): void
    {
        $this->getAcls('write_products')->shouldReturn([
            'pim_api_product_edit',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_delete_product_scope(): void
    {
        $this->getAcls('delete_products')->shouldReturn([
            'pim_api_product_remove',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_message_that_correspond_to_the_read_product_scope(): void
    {
        $this->getMessage('read_products')->shouldReturn([
            'icon' => 'products',
            'type' => 'view',
            'entities' => 'products',
        ]);
    }

    public function it_provides_message_that_correspond_to_write_product_scope(): void
    {
        $this->getMessage('write_products')->shouldReturn([
            'icon' => 'products',
            'type' => 'edit',
            'entities' => 'products',
        ]);
    }

    public function it_provides_message_that_correspond_to_the_delete_product_scope(): void
    {
        $this->getMessage('delete_products')->shouldReturn([
            'icon' => 'products',
            'type' => 'delete',
            'entities' => 'products',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->getMessage('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_product_scope(): void
    {
        $this->getLowerHierarchyScopes('read_products')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_product_scope(): void
    {
        $this->getLowerHierarchyScopes('write_products')->shouldReturn([
            'read_products',
        ]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_delete_product_scope(): void
    {
        $this->getLowerHierarchyScopes('delete_products')->shouldReturn([
            'read_products',
            'write_products',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->getLowerHierarchyScopes('unknown_scope')->shouldReturn([]);
    }
}
