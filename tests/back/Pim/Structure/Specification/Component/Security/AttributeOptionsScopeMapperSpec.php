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
            'pim_api_attribute_option_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_attribute_options_scope(): void
    {
        $this->getAcls('write_attribute_options')->shouldReturn([
            'pim_api_attribute_option_edit',
        ]);
    }

    public function it_throws_an_exception_when_trying_to_get_acls_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getAcls', ['unknown_scope']);
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

    public function it_throws_an_exception_when_trying_to_get_message_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getMessage', ['unknown_scope']);
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

    public function it_throws_an_exception_when_trying_to_get_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getLowerHierarchyScopes', ['unknown_scope']);
    }
}
