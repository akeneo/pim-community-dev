<?php

namespace spec\Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\UserBundle\Entity\Role;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\Updater\RoleUpdater;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class RoleUpdaterSpec extends ObjectBehavior
{
    function let(
        AclManager $aclManager
    ) {
        $this->beConstructedWith($aclManager);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(RoleUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_creates_a_role(
        Role $role,
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

    function it_updates_a_role_label_but_never_the_role_code(
        Role $role,
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

    function it_throws_an_exception_if_tries_to_update_anything_than_a_role()
    {
        $this->shouldThrow(InvalidObjectException::class)
            ->during('update', [new \StdClass(), []]);
    }
}
