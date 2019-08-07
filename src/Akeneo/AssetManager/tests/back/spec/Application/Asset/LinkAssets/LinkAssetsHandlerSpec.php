<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\LinkAssets;

use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkMultipleAssetsCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\ProductLinkRuleLauncherInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LinkAssetsHandlerSpec extends ObjectBehavior
{
    public function let(ProductLinkRuleLauncherInterface $productLinkRuleLauncher)
    {
        $this->beConstructedWith($productLinkRuleLauncher);
    }

    public function it_handles_a_link_multiple_assets_command(
        ProductLinkRuleLauncherInterface $productLinkRuleLauncher
    ) {
        $linkAssetCommand_A = new LinkAssetCommand();
        $linkAssetCommand_A->assetFamilyIdentifier = 'frontview';
        $linkAssetCommand_A->assetCode = 'switch';

        $linkAssetCommand_B = new LinkAssetCommand();
        $linkAssetCommand_B->assetFamilyIdentifier = 'frontview';
        $linkAssetCommand_B->assetCode = 'ps4';

        $linkMultipleAssetsCommand = new LinkMultipleAssetsCommand();
        $linkMultipleAssetsCommand->linkAssetCommands = [
            $linkAssetCommand_A,
            $linkAssetCommand_B
        ];

        $productLinkRuleLauncher->launch(
            AssetFamilyIdentifier::fromString('frontview'),
            [
                AssetCode::fromString('switch'),
                AssetCode::fromString('ps4'),
            ]
        )->shouldBeCalled();

        $this->handle($linkMultipleAssetsCommand);
    }

    public function it_groups_launches_by_family_identifiers(
        ProductLinkRuleLauncherInterface $productLinkRuleLauncher
    ) {
        $linkAssetCommand_A = new LinkAssetCommand();
        $linkAssetCommand_A->assetFamilyIdentifier = 'frontview';
        $linkAssetCommand_A->assetCode = 'switch';

        $linkAssetCommand_B = new LinkAssetCommand();
        $linkAssetCommand_B->assetFamilyIdentifier = 'frontview';
        $linkAssetCommand_B->assetCode = 'ps4';

        $linkAssetCommand_C = new LinkAssetCommand();
        $linkAssetCommand_C->assetFamilyIdentifier = 'video';
        $linkAssetCommand_C->assetCode = 'ps4';

        $linkMultipleAssetsCommand = new LinkMultipleAssetsCommand();
        $linkMultipleAssetsCommand->linkAssetCommands = [
            $linkAssetCommand_A,
            $linkAssetCommand_B,
            $linkAssetCommand_C
        ];

        $productLinkRuleLauncher->launch(
            AssetFamilyIdentifier::fromString('frontview'),
            [
                AssetCode::fromString('switch'),
                AssetCode::fromString('ps4'),
            ]
        )->shouldBeCalled();

        $productLinkRuleLauncher->launch(
            AssetFamilyIdentifier::fromString('video'),
            [
                AssetCode::fromString('ps4'),
            ]
        )->shouldBeCalled();

        $this->handle($linkMultipleAssetsCommand);
    }

    public function it_launches_nothing_if_the_command_is_empty(
        ProductLinkRuleLauncherInterface $productLinkRuleLauncher
    ) {
        $linkMultipleAssetsCommand = new LinkMultipleAssetsCommand();

        $productLinkRuleLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->handle($linkMultipleAssetsCommand);
    }
}
