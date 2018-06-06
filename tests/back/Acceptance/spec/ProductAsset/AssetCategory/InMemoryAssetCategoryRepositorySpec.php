<?php

namespace spec\AkeneoEnterprise\Test\Acceptance\ProductAsset\AssetCategory;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use AkeneoEnterprise\Test\Acceptance\ProductAsset\AssetCategory\InMemoryAssetCategoryRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\Category;
use Akeneo\Asset\Component\Repository\AssetCategoryRepositoryInterface;
use Prophecy\Argument;

class InMemoryAssetCategoryRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryAssetCategoryRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_is_an_asset_repository()
    {
        $this->shouldBeAnInstanceOf(AssetCategoryRepositoryInterface::class);
    }

    function it_asserts_the_identifier_property_is_the_code()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_category_by_identifier()
    {
        $category = new Category();
        $category->setCode('a-category');
        $this->beConstructedWith([$category->getCode() => $category]);

        $this->findOneByIdentifier('a-category')->shouldReturn($category);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-category')->shouldReturn(null);
    }

    function it_saves_a_category()
    {
        $category = new Category();
        $category->setCode('a-category');

        $this->save($category)->shouldReturn(null);

        $this->findOneByIdentifier($category->getCode())->shouldReturn($category);
    }

    function it_saves_only_categories()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }
}
