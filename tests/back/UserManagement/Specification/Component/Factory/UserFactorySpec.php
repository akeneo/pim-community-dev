<?php

namespace Specification\Akeneo\UserManagement\Component\Factory;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider;
use Akeneo\Tool\Component\Classification\Model\Category;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Factory\DefaultProperty;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserFactorySpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository,
        RoleRepositoryInterface $roleRepository,
        DefaultProperty $defaultProperty1,
        DefaultProperty $defaultProperty2
    ) {
        $this->beConstructedWith(
            $localeRepository,
            $channelRepository,
            $categoryRepository,
            $groupRepository,
            $roleRepository,
            User::class,
            $defaultProperty1,
            $defaultProperty2
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserFactory::class);
    }

    function it_is_a_default_factory()
    {
        $this->shouldImplement(SimpleFactoryInterface::class);
    }

    function it_creates_a_user(
        User $user,
        $localeRepository,
        $channelRepository,
        $categoryRepository,
        $groupRepository,
        $roleRepository,
        $defaultProperty1,
        $defaultProperty2
    ) {
        $locale = new Locale();
        $channel = new Channel();
        $category = new Category();
        $group = new Group();
        $role = new Role('ROLE_USER');

        $localeRepository->getActivatedLocales()->willReturn([$locale]);
        $localeRepository->findOneBy(['code' => UiLocaleProvider::MAIN_LOCALE])->willReturn($locale);

        $channelRepository->findOneBy([])->willReturn($channel);

        $categoryRepository->getTrees()->willReturn([$category]);

        $groupRepository->findOneByIdentifier('all')->willReturn($group);
        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);

        $defaultProperty1->mutate(Argument::type(User::class))->willReturn($user);
        $defaultProperty2->mutate(Argument::type(User::class))->willReturn($user);

        $defaultProperty1->mutate(Argument::type(User::class))->shouldBeCalled();
        $defaultProperty2->mutate(Argument::type(User::class))->shouldBeCalled();

        $this->create()->shouldReturnAnInstanceOf(User::class);
    }
}
