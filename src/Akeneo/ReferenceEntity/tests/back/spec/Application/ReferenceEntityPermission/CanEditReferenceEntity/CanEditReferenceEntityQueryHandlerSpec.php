<?php

namespace spec\Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CanEditReferenceEntityQueryHandlerSpec extends ObjectBehavior
{
    function let(
        ReferenceEntityPermissionRepositoryInterface $referenceEntityPermissionRepository,
        FindUserGroupsForSecurityIdentifierInterface $findUserGroupsForSecurityIdentifier
    ) {
        $this->beConstructedWith($referenceEntityPermissionRepository, $findUserGroupsForSecurityIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CanEditReferenceEntityQueryHandler::class);
    }

    function it_asks_the_reference_entity_permission_if_the_user_is_allowed(
        ReferenceEntityPermissionRepositoryInterface $referenceEntityPermissionRepository,
        FindUserGroupsForSecurityIdentifierInterface $findUserGroupsForSecurityIdentifier,
        ReferenceEntityPermission $referenceEntityPermission,
        UserGroupIdentifier $userGroupIdentifier1,
        UserGroupIdentifier $userGroupIdentifier2
    ) {
        $query = new CanEditReferenceEntityQuery();
        $query->referenceEntityIdentifier = 'brand';
        $query->securityIdentifier = 'julia';

        $referenceEntityPermissionRepository->getByReferenceEntityIdentifier(
            Argument::that(
                function ($referenceEntityIdentifier) {
                    return 'brand' === $referenceEntityIdentifier->normalize();
                }
            )
        )->willReturn($referenceEntityPermission);

        $findUserGroupsForSecurityIdentifier->__invoke(
            Argument::that(
                function (SecurityIdentifier $securityIdentifier) {
                    return 'julia' === $securityIdentifier->stringValue();
                }
            )
        )->willReturn([$userGroupIdentifier1, $userGroupIdentifier2]);

        $referenceEntityPermission->isAllowedToEdit([$userGroupIdentifier1, $userGroupIdentifier2])->willReturn(true);

        $this->__invoke($query)->shouldReturn(true);
    }
}
