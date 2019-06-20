<?php

namespace spec\Akeneo\AssetManager\Application\Asset\DeleteAsset;

use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAssetHandlerSpec extends ObjectBehavior
{
    public function let(AssetRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteAssetHandler::class);
    }

    function it_deletes_a_asset_by_its_code_and_entity_identifier(AssetRepositoryInterface $repository)
    {
        $command = new DeleteAssetCommand(
            'asset_code',
            'entity_identifier'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('entity_identifier');
        $assetCode = AssetCode::fromString('asset_code');

        $repository->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->shouldBeCalled();

        $this->__invoke($command);
    }
}
