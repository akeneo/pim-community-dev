<?php

namespace spec\Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationLauncherInterface;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsCommand;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
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
        $command->getAssetCodes()->willReturn(['assetCode1', 'assetCode2']);

        $computeTransformationLauncher
            ->launch([AssetCode::fromString('assetCode1'), AssetCode::fromString('assetCode2')])
            ->shouldBeCalledOnce();

        $this->handle($command);
    }
}
