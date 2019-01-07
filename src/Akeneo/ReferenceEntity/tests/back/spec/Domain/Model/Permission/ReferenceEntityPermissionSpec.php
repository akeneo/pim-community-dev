<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Permission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupPermission;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class ReferenceEntityPermissionSpec extends ObjectBehavior
{
    function it_normalizes_itself()
    {
        $this->beConstructedThrough('create', [
            ReferenceEntityIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(12), RightLevel::fromString('edit')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(5), RightLevel::fromString('view')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(2), RightLevel::fromString('view')),
            ],
        ]);

        $this->normalize()->shouldReturn([
            'reference_entity_identifier' => 'designer',
            'permissions'                 => [
                [
                    'user_group_identifier' => 12,
                    'right_level'           => 'edit',
                ],
                [
                    'user_group_identifier' => 5,
                    'right_level'           => 'view',
                ],
                [
                    'user_group_identifier' => 2,
                    'right_level'           => 'view',
                ],
            ],
        ]);
    }

    function it_allows_to_edit_a_reference_entity_if_there_are_no_permissions_set_for_it()
    {
        $this->beConstructedThrough('create', [ReferenceEntityIdentifier::fromString('designer'), []]);

        $this->isAllowedToEdit([UserGroupIdentifier::fromInteger(1)])->shouldReturn(true);
        $this->isAllowedToEdit([UserGroupIdentifier::fromInteger(2)])->shouldReturn(true);
    }

    function it_allows_to_edit_a_reference_entity_if_the_user_is_member_of_at_least_one_group_that_is_allowed_to_edit()
    {
        $this->beConstructedThrough('create', [
            ReferenceEntityIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(1), RightLevel::fromString('edit')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(2), RightLevel::fromString('view')),
            ],
        ]);

        $this->isAllowedToEdit([UserGroupIdentifier::fromInteger(1)])->shouldReturn(true);
        $this->isAllowedToEdit([UserGroupIdentifier::fromInteger(2)])->shouldReturn(false);
        $this->isAllowedToEdit([
            UserGroupIdentifier::fromInteger(1),
            UserGroupIdentifier::fromInteger(2),
        ])->shouldReturn(true);
        $this->isAllowedToEdit([
            UserGroupIdentifier::fromInteger(2),
            UserGroupIdentifier::fromInteger(4)
        ])->shouldReturn(false);
    }

    function it_throws_if_the_given_parameters_is_not_an_array_of_group_identifiers_for_to_check_rights()
    {
        $this->beConstructedThrough('create', [ReferenceEntityIdentifier::fromString('designer'), []]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('isAllowedToEdit', [[1, 2]]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('isAllowedToEdit', [[new \stdClass()]]);
    }

    function it_throws_an_error_if_the_same_user_group_is_used_twice()
    {
        $this->beConstructedThrough('create', [
            ReferenceEntityIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(12), RightLevel::fromString('edit')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(5), RightLevel::fromString('view')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(5), RightLevel::fromString('view')),
            ],
        ]);

        $this->shouldThrow('InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_an_exception_if_permissions_are_of_the_wrong_type()
    {
        $this->beConstructedThrough('create', [
            ReferenceEntityIdentifier::fromString('designer'),
            [
                new \stdClass(),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(12), RightLevel::fromString('edit')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(5), RightLevel::fromString('view')),
            ],
        ]);

        $this->shouldThrow('InvalidArgumentException')->duringInstantiation();
    }
}
