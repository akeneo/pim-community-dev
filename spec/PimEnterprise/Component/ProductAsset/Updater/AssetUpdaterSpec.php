<?php

namespace spec\PimEnterprise\Component\ProductAsset\Updater;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\Classification\Repository\TagRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Prophecy\Argument;

class AssetUpdaterSpec extends ObjectBehavior
{
    function let(
        TagRepositoryInterface $tagRepository,
        CategoryRepositoryInterface $categoryRepository,
        AssetFactory $assetFactory
    ) {
        $this->beConstructedWith($tagRepository, $categoryRepository, $assetFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Updater\AssetUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_asset()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "PimEnterprise\Component\ProductAsset\Model\AssetInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_an_asset(
        $tagRepository,
        $categoryRepository,
        AssetInterface $asset,
        TagInterface $tag1,
        TagInterface $tag2,
        CategoryInterface $cat1,
        CategoryInterface $cat2
    ) {
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();
        $asset->setEndOfUseAt(new \DateTime('2018-02-01T00:00:00+01:00'))->shouldBeCalled();

        $tagRepository->findOneByIdentifier('dog')->willReturn($tag1);
        $tagRepository->findOneByIdentifier('flowers')->willReturn($tag2);

        $categoryRepository->findOneByIdentifier('cat1')->willReturn($cat1);
        $categoryRepository->findOneByIdentifier('cat2')->willReturn($cat2);

        $asset->getTagCodes()->willReturn([]);
        $asset->getCategoryCodes()->willReturn([]);
        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->addTag($tag1)->shouldBeCalled();
        $asset->addTag($tag2)->shouldBeCalled();

        $asset->addCategory($cat1)->shouldBeCalled();
        $asset->addCategory($cat2)->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog', 'flowers'],
            'categories'  => ['cat1', 'cat2'],
            'end_of_use'  => '2018-02-01T00:00:00+01:00',
        ];

        $this->update($asset, $values, []);
    }

    function it_throws_an_exception_if_tag_does_not_exist($tagRepository, AssetInterface $asset)
    {
        $tagRepository->findOneByIdentifier('dog')->willReturn(null);

        $asset->getTagCodes()->willReturn('');
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();

        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog'],
            'categories'  => ['cat1'],
            'end_of_use'  => '2018-02-01',
        ];

        $this
            ->shouldThrow(
                new \InvalidArgumentException('Tag with "dog" code does not exist')
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_category_does_not_exist(
        $tagRepository,
        $categoryRepository,
        AssetInterface $asset,
        TagInterface $tag1
    ) {
        $tagRepository->findOneByIdentifier('dog')->willReturn($tag1);
        $categoryRepository->findOneByIdentifier('cat1')->willReturn(null);

        $asset->getCategoryCodes()->willReturn([]);
        $asset->getTagCodes()->willReturn([]);
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();

        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->addTag($tag1)->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog'],
            'categories'  => ['cat1'],
            'end_of_use'  => '2018-02-01',
        ];

        $this
            ->shouldThrow(
                new \InvalidArgumentException('Category with "cat1" code does not exist')
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_date_format_is_invalid(
        $tagRepository,
        $categoryRepository,
        AssetInterface $asset,
        TagInterface $tag1,
        TagInterface $tag2,
        CategoryInterface $cat1,
        CategoryInterface $cat2
    ) {
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();
        $asset->getTagCodes()->willReturn([]);
        $asset->getCategoryCodes()->willReturn([]);

        $tagRepository->findOneByIdentifier('dog')->willReturn($tag1);
        $tagRepository->findOneByIdentifier('flowers')->willReturn($tag2);

        $categoryRepository->findOneByIdentifier('cat1')->willReturn($cat1);
        $categoryRepository->findOneByIdentifier('cat2')->willReturn($cat2);

        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->addTag($tag1)->shouldBeCalled();
        $asset->addTag($tag2)->shouldBeCalled();

        $asset->addCategory($cat1)->shouldBeCalled();
        $asset->addCategory($cat2)->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog', 'flowers'],
            'categories'  => ['cat1', 'cat2'],
            'end_of_use'  => '2018/02/01',
        ];

        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'Asset expects a string with the format "yyyy-mm-dd" as data, "2018/02/01" given'
                )
            )
            ->during('update', [$asset, $values, []]);
    }
}
