<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\AssetFamily\DeleteAssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\DeleteAssetFamily\DeleteAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\DeleteAssetFamily\DeleteAssetFamilyHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeleteAssetFamilyHandlerSpec extends ObjectBehavior
{
    function let(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->beConstructedWith($assetFamilyRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteAssetFamilyHandler::class);
    }

    function it_deletes_an_asset_family(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        DeleteAssetFamilyCommand $command
    ) {
        $command->identifier = 'brand';

        $assetFamilyRepository->deleteByIdentifier(
            Argument::type(AssetFamilyIdentifier::class)
        )->shouldBeCalled();

        $this->__invoke($command);
    }
}
