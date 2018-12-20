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
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(2), RightLevel::fromString('none')),
            ]
        ]);

        $this->normalize()->shouldReturn([
            'reference_entity_identifier' => 'designer',
            'permissions' => [
                [
                    'user_group_identifier' => 12,
                    'right_level' => 'edit',
                ],
                [
                    'user_group_identifier' => 5,
                    'right_level' => 'view',
                ],
                [
                    'user_group_identifier' => 2,
                    'right_level' => 'none',
                ],
            ]
        ]);
    }
    
    function it_throws_an_error_if_the_same_user_group_is_used_twice()
    {
        $this->beConstructedThrough('create', [
            ReferenceEntityIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(12), RightLevel::fromString('edit')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(5), RightLevel::fromString('view')),
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(5), RightLevel::fromString('none')),
            ]
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
                UserGroupPermission::create(UserGroupIdentifier::fromInteger(5), RightLevel::fromString('none')),
            ]
        ]);

        $this->shouldThrow('InvalidArgumentException')->duringInstantiation();
    }
}
