<?php

namespace spec\Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\User\Updater\UserUpdater;

class UserUpdaterSpec extends ObjectBehavior
{
    function let(
        UserManager $userManager,
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $roleRepository,
        IdentifiableObjectRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $userManager,
            $categoryRepository,
            $localeRepository,
            $channelRepository,
            $roleRepository,
            $groupRepository
        );
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_sets_data(
        $roleRepository,
        $groupRepository,
        UserInterface $user,
        Role $role,
        Group $groupManager,
        Group $groupAll
    ) {
        $data = [
            'username' => 'julia',
            'first_name' => 'Julia',
            'last_name' => 'Stark',
            'email' => 'julia@example.net',
            'password' => 'julia',
            'phone' => '0655443322',
            'roles' => ['MANAGER'],
            'groups' => ['Manager'],
            'enabled' => false
        ];

        $user->setUsername('julia')->willReturn($user);
        $user->setFirstName('Julia')->willReturn($user);
        $user->setLastName('Stark')->willReturn($user);
        $user->setEmail('julia@example.net')->willReturn($user);
        $user->setPassword('julia')->willReturn($user);
        $user->setPlainPassword('julia')->willReturn($user);
        $user->setPhone('0655443322')->willReturn($user);
        $user->setRoles(['MANAGER'])->willReturn($user);
        $user->setEnabled(false)->willReturn($user);

        $roleRepository->findOneByIdentifier('MANAGER')->willReturn($role);
        $user->addRole($role)->willReturn($user);

        $groupRepository->findOneByIdentifier('Manager')->willReturn($groupManager);
        $user->addGroup($groupManager)->willReturn($user);
        $groupManager->__toString()->willReturn('Manager');

        $user->hasGroup('all')->willReturn(false);
        $groupRepository->findOneByIdentifier('all')->willReturn($groupAll);
        $user->addGroup($groupAll)->willReturn($user);

        $this->update($user, $data, [])->shouldReturn($this);
    }

    function it_throws_an_exception_if_catalog_locale_does_not_exist(UserInterface $user)
    {
        $data = [
            'catalog_locale' => 'unknown',
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'catalog_locale',
                'locale code',
                'The locale does not exist',
                UserUpdater::class,
                'unknown'
            )
        )->during(
            'update', [$user, $data, []]
        );
    }

    function it_throws_an_exception_if_category_does_not_exist(UserInterface $user)
    {
        $data = [
            'default_tree' => 'unknown',
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'default_tree',
                'category code',
                'The category does not exist',
                UserUpdater::class,
                'unknown'
            )
        )->during(
            'update', [$user, $data, []]
        );
    }

    function it_throws_an_exception_if_channel_does_not_exist(UserInterface $user)
    {
        $data = [
            'catalog_scope' => 'unknown',
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'catalog_scope',
                'channel code',
                'The channel does not exist',
                UserUpdater::class,
                'unknown'
            )
        )->during(
            'update', [$user, $data, []]
        );
    }

    function it_throws_an_exception_if_role_does_not_exist(UserInterface $user)
    {
        $data = [
            'roles' => ['unknown'],
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'roles',
                'role',
                'The role does not exist',
                UserUpdater::class,
                'unknown'
            )
        )->during(
            'update', [$user, $data, []]
        );
    }

    function it_throws_an_exception_if_group_does_not_exist(UserInterface $user)
    {
        $data = [
            'groups' => ['unknown'],
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'groups',
                'group',
                'The group does not exist',
                UserUpdater::class,
                'unknown'
            )
        )->during(
            'update', [$user, $data, []]
        );
    }

    function it_throws_an_exception_if_field_is_unknown(UserInterface $user)
    {
        $data = [
            'unknown' => 'julia',
        ];

        $this->shouldThrow(
            UnknownPropertyException::unknownProperty('unknown')
        )->during(
            'update', [$user, $data, []]
        );
    }
}
