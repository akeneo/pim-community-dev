<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\ReferenceEntity\Infrastructure\Security\ReferenceEntityScopeMapper;
use PhpSpec\ObjectBehavior;

class ReferenceEntityScopeMapperSpec extends ObjectBehavior
{
    public function it_is_a_reference_entity_scope_mapper(): void
    {
        $this->shouldHaveType(ReferenceEntityScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getScopes()->shouldReturn([
            'read_reference_entities',
            'write_reference_entities',
            'read_reference_entity_records',
            'write_reference_entity_records',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_reference_entities_scope(): void
    {
        $this->getAcls('read_reference_entities')->shouldReturn([
            'pim_api_reference_entity_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_reference_entities_scope(): void
    {
        $this->getAcls('write_reference_entities')->shouldReturn([
            'pim_api_reference_entity_edit',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_reference_entity_records_scope(): void
    {
        $this->getAcls('read_reference_entity_records')->shouldReturn([
            'pim_api_reference_entity_record_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_reference_entity_records_scope(): void
    {
        $this->getAcls('write_reference_entity_records')->shouldReturn([
            'pim_api_reference_entity_record_edit',
        ]);
    }

    public function it_throws_an_exception_when_trying_to_get_acl_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getAcls', ['unknown_scope']);
    }

    public function it_provides_message_that_corresponds_to_the_read_reference_entities_scope(): void
    {
        $this->getMessage('read_reference_entities')->shouldReturn([
            'icon' => 'reference_entity',
            'type' => 'view',
            'entities' => 'reference_entity',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_reference_entities_scope(): void
    {
        $this->getMessage('write_reference_entities')->shouldReturn([
            'icon' => 'reference_entity',
            'type' => 'edit',
            'entities' => 'reference_entity',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_read_reference_entity_records_scope(): void
    {
        $this->getMessage('read_reference_entity_records')->shouldReturn([
            'icon' => 'reference_entity_record',
            'type' => 'view',
            'entities' => 'reference_entity_record',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_reference_entity_records_scope(): void
    {
        $this->getMessage('write_reference_entity_records')->shouldReturn([
            'icon' => 'reference_entity_record',
            'type' => 'edit',
            'entities' => 'reference_entity_record',
        ]);
    }

    public function it_throws_an_exception_when_trying_to_get_message_if_an_unknown_scope_is_given(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getMessage', ['unknown_scope']);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_reference_entities_scope(): void
    {
        $this->getLowerHierarchyScopes('read_reference_entities')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_reference_entities_scope(): void
    {
        $this->getLowerHierarchyScopes('write_reference_entities')->shouldReturn([
            'read_reference_entities',
        ]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_reference_entity_records_scope(): void
    {
        $this->getLowerHierarchyScopes('read_reference_entity_records')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_reference_entity_records_scope(): void
    {
        $this->getLowerHierarchyScopes('write_reference_entity_records')->shouldReturn([
            'read_reference_entity_records',
        ]);
    }

    public function it_throws_an_exception_when_trying_to_get_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'))
            ->during('getLowerHierarchyScopes', ['unknown_scope']);
    }
}
