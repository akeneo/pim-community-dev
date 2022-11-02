<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetIdentifiersLauncherInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ComputeTransformationFromAssetIdentifiersLauncherSpec extends ObjectBehavior
{
    function let(PublishJobToQueue $publishJobToQueue, TokenStorageInterface $tokenStorage, TokenInterface $token)
    {
        $token->getUserIdentifier()->willReturn('julia');
        $tokenStorage->getToken()->willReturn($token);
        $this->beConstructedWith($publishJobToQueue, $tokenStorage);
    }

    function it_is_a_compute_transformations_job_launcher()
    {
        $this->shouldImplement(ComputeTransformationFromAssetIdentifiersLauncherInterface::class);
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
                'asset_identifiers' => ['assetIdentifier1', 'assetIdentifier2']
            ],
            false,
            'julia'
        )->shouldBeCalledOnce();

        $this->launch([AssetIdentifier::fromString('assetIdentifier1'), AssetIdentifier::fromString('assetIdentifier2')]);
    }
}
