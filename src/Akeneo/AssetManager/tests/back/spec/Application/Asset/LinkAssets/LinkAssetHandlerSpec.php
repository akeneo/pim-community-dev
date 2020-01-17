<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\LinkAssets;

use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\ProductLinkRuleLauncherInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

class LinkAssetHandlerSpec extends ObjectBehavior
{
    public function let(ProductLinkRuleLauncherInterface $productLinkRuleLauncher)
    {
        $this->beConstructedWith($productLinkRuleLauncher);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(LinkAssetHandler::class);
    }

    function it_handles_a_single_link_asset_command(ProductLinkRuleLauncherInterface $productLinkRuleLauncher)
    {
        $productLinkRuleLauncher->launch(AssetFamilyIdentifier::fromString('family'), [AssetCode::fromString('code')])
            ->shouldBeCalled();

        $linkAssetCommand = new LinkAssetCommand();
        $linkAssetCommand->assetFamilyIdentifier = 'family';
        $linkAssetCommand->assetCode = 'code';

        $this->__invoke($linkAssetCommand);
    }
}
