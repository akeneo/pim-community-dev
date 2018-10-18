<?php

namespace Specification\Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Updater\RoleUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class RoleUpdaterSpec extends ObjectBehavior
{
    function let(AclManager $aclManager)
    {
        $this->beConstructedWith($aclManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RoleUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_role_label_but_never_the_role_code(
        RoleInterface $role,
        SecurityIdentityInterface $sid,
        $aclManager
    )
    {
        $role->getRole()->willReturn('ROLE_ADMINISTRATOR');
        $role->setRole('ROLE_ADMINISTRATOR')->shouldNotBeCalled();
        $role->setLabel('admin')->shouldBeCalled();

        $role->getRole()->willReturn('ROLE_ADMINISTRATOR');
        $aclManager->getSid($role)->willReturn($sid);
        $aclManager->getAllExtensions()->willReturn([]);

        $aclManager->flush()->shouldBeCalled();

        $this->update(
            $role,
            [
                'role' => 'ROLE_ADMINISTRATOR',
                'label' => 'admin'
            ]
        );
    }

    function it_throws_an_exception_if_the_given_object_is_not_a_role()
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [
            new Group(),
            [
                'name' => 'name',
            ],
        ]);
    }

    function it_creates_a_role(
        RoleInterface $role,
        SecurityIdentityInterface $sid,
        $aclManager
    )
    {
        $role->getRole()->willReturn(null);
        $role->setRole('ROLE_ADMINISTRATOR')->shouldBeCalled();
        $role->setLabel('administrator')->shouldBeCalled();

        $aclManager->getSid($role)->willReturn($sid);
        $aclManager->getAllExtensions()->willReturn([]);

        $aclManager->flush()->shouldBeCalled();

        $this->update(
            $role,
            [
                'role' => 'ROLE_ADMINISTRATOR',
                'label' => 'administrator'
            ]
        );
    }
}
