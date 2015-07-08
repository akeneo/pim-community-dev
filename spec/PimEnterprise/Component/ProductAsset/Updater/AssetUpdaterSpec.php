<?php

namespace spec\PimEnterprise\Component\ProductAsset\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Component\Classification\Repository\TagRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Prophecy\Argument;

class AssetUpdaterSpec extends ObjectBehavior
{
    function let(
        TagRepositoryInterface $repository
    ) {
        $this->beConstructedWith($repository);
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

    function it_updates_a_asset($repository, AssetInterface $asset, TagInterface $tag1, TagInterface $tag2)
    {
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();
        $asset->setEndOfUseAt(new \DateTime('2018-02-01'))->shouldBeCalled();

        $repository->findOneByIdentifier('dog')->willReturn($tag1);
        $repository->findOneByIdentifier('flowers')->willReturn($tag2);

        $asset->addTag($tag1)->shouldBeCalled();
        $asset->addTag($tag2)->shouldBeCalled();
        $asset->getId()->willReturn(null);

        $values = [
            'code'          => 'mycode',
            'description'   => 'My awesome description',
            'tags'          => ['dog', 'flowers'],
            'end_of_use_at' => '2018-02-01',
        ];

        $this->update($asset, $values, []);
    }

    function it_throws_an_exception_if_tag_does_not_exist($repository, AssetInterface $asset)
    {
        $repository->findOneByIdentifier('dog')->willReturn(null);

        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $values = [
            'code'          => 'mycode',
            'description'   => 'My awesome description',
            'tags'          => ['dog'],
            'end_of_use_at' => '2018-02-01',
        ];

        $this
            ->shouldThrow(
                new \InvalidArgumentException('Tag with "dog" code does not exist')
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_date_format_is_invalid(
        $repository,
        AssetInterface $asset,
        TagInterface $tag1,
        TagInterface $tag2
    ) {
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();

        $repository->findOneByIdentifier('dog')->willReturn($tag1);
        $repository->findOneByIdentifier('flowers')->willReturn($tag2);

        $asset->addTag($tag1)->shouldBeCalled();
        $asset->addTag($tag2)->shouldBeCalled();
        $asset->getId()->willReturn(null);

        $values = [
            'code'          => 'mycode',
            'description'   => 'My awesome description',
            'tags'          => ['dog', 'flowers'],
            'end_of_use_at' => '2018/02/01',
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
