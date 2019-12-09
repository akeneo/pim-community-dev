<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformationFromAssetFamilyIdentifierLauncher;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use PhpSpec\ObjectBehavior;

class ComputeTransformationFromAssetFamilyIdentifierLauncherSpec extends ObjectBehavior
{
    function let(PublishJobToQueue $publishJobToQueue) {
        $this->beConstructedWith($publishJobToQueue);
        $this->shouldHaveType(ComputeTransformationFromAssetFamilyIdentifierLauncher::class);
    }

    function it_publishes_the_job(PublishJobToQueue $publishJobToQueue)
    {
        $publishJobToQueue->publish(
            'asset_manager_compute_transformations', [
                'asset_family_identifier' => 'packshot'
            ]
        )->shouldBeCalledOnce();

        $this->launch(AssetFamilyIdentifier::fromString('packshot'));
    }
}
