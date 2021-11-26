<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Structure\Component\Security\AttributeOptionsScopeMapper;
use PhpSpec\ObjectBehavior;

class AttributeOptionsScopeMapperSpec extends ObjectBehavior
{
    public function it_is_an_attribute_options_scope_mapper(): void
    {
        $this->shouldHaveType(AttributeOptionsScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getScopes()->shouldReturn([
            'read_attribute_options',
            'write_attribute_options',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_attribute_options_scope(): void
    {
        $this->getAcls('read_attribute_options')->shouldReturn([
            'pim_api_attribute_options_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_attribute_options_scope(): void
    {
        $this->getAcls('write_attribute_options')->shouldReturn([
            'pim_api_attribute_options_edit',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_message_that_correspond_to_the_read_attribute_options_scope(): void
    {
        $this->getMessage('read_attribute_options')->shouldReturn([
            'icon' => 'attribute_options',
            'type' => 'view',
            'entities' => 'attribute_options',
        ]);
    }

    public function it_provides_message_that_correspond_to_the_write_attribute_options_scope(): void
    {
        $this->getMessage('write_attribute_options')->shouldReturn([
            'icon' => 'attribute_options',
            'type' => 'edit',
            'entities' => 'attribute_options',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->getMessage('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_attribute_options_scope(): void
    {
        $this->getLowerHierarchyScopes('read_attribute_options')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_attribute_options_scope(): void
    {
        $this->getLowerHierarchyScopes('write_attribute_options')->shouldReturn([
            'read_attribute_options',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->getLowerHierarchyScopes('unknown_scope')->shouldReturn([]);
    }
}
