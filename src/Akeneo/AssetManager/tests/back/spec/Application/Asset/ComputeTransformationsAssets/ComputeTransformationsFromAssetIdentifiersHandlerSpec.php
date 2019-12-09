<?php

namespace spec\Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetIdentifiersLauncherInterface;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsFromAssetIdentifiersCommand;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsFromAssetIdentifiersHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use PhpSpec\ObjectBehavior;

class ComputeTransformationsFromAssetIdentifiersHandlerSpec extends ObjectBehavior
{
    function let(ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher)
    {
        $this->beConstructedWith($computeTransformationLauncher);
        $this->shouldHaveType(ComputeTransformationsFromAssetIdentifiersHandler::class);
    }

    function it_handles_compute_transformations_command(
        ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher,
        ComputeTransformationsFromAssetIdentifiersCommand $command
    ) {
        $command->getAssetIdentifiers()->willReturn(['assetIdentifier1', 'assetIdentifier2']);

        $computeTransformationLauncher
            ->launch([AssetIdentifier::fromString('assetIdentifier1'), AssetIdentifier::fromString('assetIdentifier2')])
            ->shouldBeCalledOnce();

        $this->handle($command);
    }
}
