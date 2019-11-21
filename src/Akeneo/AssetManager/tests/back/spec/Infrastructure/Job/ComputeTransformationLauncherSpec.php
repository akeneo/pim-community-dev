<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformationLauncher;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use PhpSpec\ObjectBehavior;

class ComputeTransformationLauncherSpec extends ObjectBehavior
{
    function let(PublishJobToQueue $publishJobToQueue) {
        $this->beConstructedWith($publishJobToQueue);
        $this->shouldHaveType(ComputeTransformationLauncher::class);
    }

    function it_throws_an_exception_when_lauching_wrong_type()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('launch', [[new \stdClass()]]);
    }

    function it_publishes_the_job(PublishJobToQueue $publishJobToQueue)
    {
        $publishJobToQueue->publish(
            'asset_manager_compute_transformations', [
                'asset_codes' => ['assetCode1', 'assetCode2']
            ]
        )->shouldBeCalledOnce();

        $this->launch([AssetCode::fromString('assetCode1'), AssetCode::fromString('assetCode2')]);
    }
}
