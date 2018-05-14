<?php

namespace spec\PimEnterprise\Component\ProductAsset\Updater\ExternalApi;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Updater\ExternalApi\AssetUpdater;

class AssetUpdaterSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $assetUpdater)
    {
        $this->beConstructedWith($assetUpdater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_an_asset($assetUpdater, AssetInterface $asset)
    {
        $dataToUpdate = [
            'code' => 'asset_code',
            'categories' => ['category_1'],
            'localizable' => true,
            'tags' => ['tag_1'],
            'description' => 'desc',
            'end_of_use' => '2016-09-01T00:00:00+0800',
            'variation_files' => [[
                'locale' => 'en_US',
                'channel' => 'ecommerce',
                'code' => 'my/code'
            ]],
            'reference_files' => [[
                'locale' => 'en_US',
                'channel' => 'ecommerce',
                'code' => 'my/code'
            ]],
            'unknown_property' => 'unknown'
        ];

        $assetUpdater->update($asset, [
            'code' => 'asset_code',
            'categories' => ['category_1'],
            'localizable' => true,
            'tags' => ['tag_1'],
            'description' => 'desc',
            'end_of_use' => '2016-09-01T00:00:00+0800',
            'unknown_property' => 'unknown'
        ], [])->shouldBeCalled();

        $this
            ->shouldNotThrow(\Exception::class)
            ->during('update', [$asset, $dataToUpdate, []]);
    }

    function it_updates_as_not_localizable_as_default_value_when_creating_asset($assetUpdater, AssetInterface $asset)
    {
        $dataToUpdate = ['code' => 'asset_code'];

        $assetUpdater->update($asset, ['code' => 'asset_code', 'localizable' => false], [])->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $this
            ->shouldNotThrow(\Exception::class)
            ->during('update', [$asset, $dataToUpdate, []]);
    }

    function it_updates_without_default_localizable_property_when_updating_asset($assetUpdater, AssetInterface $asset)
    {
        $dataToUpdate = ['code' => 'asset_code'];

        $assetUpdater->update($asset, ['code' => 'asset_code'], [])->shouldBeCalled();

        $asset->getId()->willReturn(1);

        $this
            ->shouldNotThrow(\Exception::class)
            ->during('update', [$asset, $dataToUpdate, []]);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_an_asset()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                AssetInterface::class
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }
}
