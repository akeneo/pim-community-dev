<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Structure\Component\Security\AssociationTypeScopeMapper;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociationTypeScopeMapperSpec extends ObjectBehavior
{
    public function it_is_an_association_type_scope_mapper(): void
    {
        $this->shouldHaveType(AssociationTypeScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getScopes()->shouldReturn([
            'read_association_types',
            'write_association_types',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_association_type_scope(): void
    {
        $this->getAcls('read_association_types')->shouldReturn([
            'pim_api_association_type_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_association_type_scope(): void
    {
        $this->getAcls('write_association_types')->shouldReturn([
            'pim_api_association_type_edit',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_message_that_corresponds_to_the_read_association_type_scope(): void
    {
        $this->getMessage('read_association_types')->shouldReturn([
            'icon' => 'association_types',
            'type' => 'view',
            'entities' => 'association_types',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_association_type_scope(): void
    {
        $this->getMessage('write_association_types')->shouldReturn([
            'icon' => 'association_types',
            'type' => 'edit',
            'entities' => 'association_types',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->getMessage('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_association_type_scope(): void
    {
        $this->getLowerHierarchyScopes('read_association_types')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_association_type_scope(): void
    {
        $this->getLowerHierarchyScopes('write_association_types')->shouldReturn([
            'read_association_types',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->getLowerHierarchyScopes('unknown_scope')->shouldReturn([]);
    }
}
