<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Asset;

use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetsHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkMultipleAssetsCommand;
use PhpSpec\ObjectBehavior;

class BatchAssetsToLinkSpec extends ObjectBehavior
{
    public function let(LinkAssetsHandler $linkAssetsHandler)
    {
        $this->beConstructedWith($linkAssetsHandler);
    }

    public function it_calls_link_assets_handler_by_giving_it_added_commands(
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $this->add('packshot', 'ps4');
        $this->add('packshot', 'switch');
        $this->add('video', 'ps4');

        $expectedCommand = new LinkMultipleAssetsCommand();

        $linkCommand_A = new LinkAssetCommand();
        $linkCommand_A->assetFamilyIdentifier = 'packshot';
        $linkCommand_A->assetCode = 'ps4';

        $linkCommand_B = new LinkAssetCommand();
        $linkCommand_B->assetFamilyIdentifier = 'packshot';
        $linkCommand_B->assetCode = 'switch';

        $linkCommand_C = new LinkAssetCommand();
        $linkCommand_C->assetFamilyIdentifier = 'video';
        $linkCommand_C->assetCode = 'ps4';

        $expectedCommand->linkAssetCommands = [
            $linkCommand_A, $linkCommand_B, $linkCommand_C
        ];

        $linkAssetsHandler->handle($expectedCommand)->shouldBeCalled();

        $this->runBatch();
    }

    public function it_does_not_call_the_link_asset_handler_if_the_there_is_not_asset_created(
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $linkAssetsHandler->handle()->shouldNotBeCalled();
        $this->runBatch();
    }
}
