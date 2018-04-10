<?php

namespace spec\Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Pim\Component\User\Model\Group;
use Pim\Component\User\Model\RoleInterface;
use Pim\Component\User\Updater\RoleUpdater;
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

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_the_role_properties(RoleInterface $role, SecurityIdentityInterface $sid, $aclManager)
    {
        $role->setRole('ROLE_ADMINISTRATOR')->shouldBeCalled();
        $role->getRole()->willReturn('ROLE_ADMINISTRATOR');
        $role->setLabel('name')->shouldBeCalled();

        $aclManager->getAllExtensions()->willReturn([]);
        $aclManager->getSid($role)->willReturn($sid);
        $aclManager->flush()->shouldBeCalled();

        $this->update(
            $role,
            [
                'label' => 'name',
                'role' => 'ROLE_ADMINISTRATOR',
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
}
