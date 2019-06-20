<?php

namespace spec\Akeneo\AssetManager\Application\Asset\DeleteAllAssets;

use Akeneo\AssetManager\Application\Asset\DeleteAllAssets\DeleteAllAssetFamilyAssetsCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAllAssets\DeleteAllAssetFamilyAssetsHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAllAssetFamilyAssetsHandlerSpec extends ObjectBehavior
{
    public function let(AssetRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteAllAssetFamilyAssetsHandler::class);
    }

    function it_deletes_all_entity_assets_by_their_entity_identifier(AssetRepositoryInterface $repository)
    {
        $command = new DeleteAllAssetFamilyAssetsCommand(
            'entity_identifier'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('entity_identifier');

        $repository->deleteByAssetFamily($assetFamilyIdentifier)->shouldBeCalled();

        $this->__invoke($command);
    }
}
