<?php

namespace Specification\Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Factory\DefaultAssetTree;
use Akeneo\Asset\Component\Model\Category;
use Akeneo\Asset\Component\Repository\AssetCategoryRepositoryInterface;
use Akeneo\UserManagement\Component\Factory\DefaultProperty;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class DefaultAssetTreeSpec extends ObjectBehavior
{
    function let(AssetCategoryRepositoryInterface $assetCategoryRepository)
    {
        $this->beConstructedWith($assetCategoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DefaultAssetTree::class);
    }

    function it_is_a_default_property()
    {
        $this->shouldImplement(DefaultProperty::class);
    }

    function it_mutates_the_user_with_the_default_asset_tree($assetCategoryRepository)
    {
        $user = new User();

        $category = new Category();
        $category->setCode('master');

        $assetCategoryRepository->findRoot()->willReturn([$category]);

        $this->mutate($user);

        Assert::eq('master', $user->getProperty('default_asset_tree'));
    }
}
