<?php

namespace spec\Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetFamilyIdentifierLauncherInterface;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsFromAssetFamilyIdentifierCommand;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsFromAssetFamilyIdentifierHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

class ComputeTransformationsFromAssetFamilyIdentifierHandlerSpec extends ObjectBehavior
{
    function let(ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationLauncher)
    {
        $this->beConstructedWith($computeTransformationLauncher);
        $this->shouldHaveType(ComputeTransformationsFromAssetFamilyIdentifierHandler::class);
    }

    function it_handles_compute_transformations_command(
        ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationLauncher,
        ComputeTransformationsFromAssetFamilyIdentifierCommand $command
    ) {
        $command->getAssetFamilyIdentifier()->willReturn('packshot');

        $computeTransformationLauncher
            ->launch(AssetFamilyIdentifier::fromString('packshot'))
            ->shouldBeCalledOnce();

        $this->handle($command);
    }
}
