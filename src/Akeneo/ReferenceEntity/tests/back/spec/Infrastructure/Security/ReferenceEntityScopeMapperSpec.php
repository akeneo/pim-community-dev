<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Security;

use Akeneo\ReferenceEntity\Infrastructure\Security\ReferenceEntityScopeMapper;
use Akeneo\Tool\Component\Api\Security\ScopeMapperInterface;
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
        $this->getAllScopes()->shouldReturn([
            'read_entities',
            'write_entities',
            'read_entity_records',
            'write_entity_records',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_entities_scope(): void
    {
        $this->getAcls('read_entities')->shouldReturn([
            'pim_api_reference_entity_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_entities_scope(): void
    {
        $this->getAcls('write_entities')->shouldReturn([
            'pim_api_reference_entity_edit',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_entity_records_scope(): void
    {
        $this->getAcls('read_entity_records')->shouldReturn([
            'pim_api_reference_entity_record_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_entity_records_scope(): void
    {
        $this->getAcls('write_entity_records')->shouldReturn([
            'pim_api_reference_entity_record_edit',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_message_that_corresponds_to_the_read_entities_scope(): void
    {
        $this->getMessage('read_entities')->shouldReturn([
            'icon' => 'reference_entity',
            'type' => 'view',
            'entities' => 'reference_entity',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_entities_scope(): void
    {
        $this->getMessage('write_entities')->shouldReturn([
            'icon' => 'reference_entity',
            'type' => 'edit',
            'entities' => 'reference_entity',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_read_entity_records_scope(): void
    {
        $this->getMessage('read_entity_records')->shouldReturn([
            'icon' => 'reference_entity_record',
            'type' => 'view',
            'entities' => 'reference_entity_record',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_entity_records_scope(): void
    {
        $this->getMessage('write_entity_records')->shouldReturn([
            'icon' => 'reference_entity_record',
            'type' => 'edit',
            'entities' => 'reference_entity_record',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->getMessage('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_entities_scope(): void
    {
        $this->getLowerHierarchyScopes('read_entities')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_entities_scope(): void
    {
        $this->getLowerHierarchyScopes('write_entities')->shouldReturn([
            'read_entities',
        ]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_entity_records_scope(): void
    {
        $this->getLowerHierarchyScopes('read_entity_records')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_entity_records_scope(): void
    {
        $this->getLowerHierarchyScopes('write_entity_records')->shouldReturn([
            'read_entity_records',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->getLowerHierarchyScopes('unknown_scope')->shouldReturn([]);
    }
}
