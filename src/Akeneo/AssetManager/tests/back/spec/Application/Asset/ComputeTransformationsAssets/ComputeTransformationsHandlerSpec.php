<?php

namespace spec\Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationLauncherInterface;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsCommand;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use PhpSpec\ObjectBehavior;

class ComputeTransformationsHandlerSpec extends ObjectBehavior
{
    function let(ComputeTransformationLauncherInterface $computeTransformationLauncher)
    {
        $this->beConstructedWith($computeTransformationLauncher);
        $this->shouldHaveType(ComputeTransformationsHandler::class);
    }

    function it_handles_compute_transformations_command(
        ComputeTransformationLauncherInterface $computeTransformationLauncher,
        ComputeTransformationsCommand $command
    ) {
        $command->getAssetIdentifiers()->willReturn(['assetIdentifier1', 'assetIdentifier2']);

        $computeTransformationLauncher
            ->launch([AssetIdentifier::fromString('assetIdentifier1'), AssetIdentifier::fromString('assetIdentifier2')])
            ->shouldBeCalledOnce();

        $this->handle($command);
    }
}
