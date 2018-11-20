<?php

namespace Specification\Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Factory\UserFactory;
use Akeneo\Asset\Component\Model\Category;
use Akeneo\Asset\Component\Repository\AssetCategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class UserFactorySpec extends ObjectBehavior
{
    function let(SimpleFactoryInterface $userFactory, AssetCategoryRepositoryInterface $assetCategoryRepository)
    {
        $this->beConstructedWith($userFactory, $assetCategoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserFactory::class);
    }

    function it_is_a_factory()
    {
        $this->shouldHaveType(SimpleFactoryInterface::class);
    }

    function it_creates_a_user($userFactory, $assetCategoryRepository)
    {
        $user = new User();

        $userFactory->create()->willReturn($user);

        $user->addProperty('default_asset_tree', 'master');

        $category = new Category();
        $category->setCode('master');

        $assetCategoryRepository->findRoot()->willReturn([$category]);

        $this->create()->shouldReturn($user);
    }
}
