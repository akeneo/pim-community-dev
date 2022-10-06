<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetFamilyIdentifierLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ComputeTransformationFromAssetFamilyIdentifierLauncherSpec extends ObjectBehavior
{
    function let(PublishJobToQueue $publishJobToQueue, TokenStorageInterface $tokenStorage, TokenInterface $token)
    {
        $token->getUserIdentifier()->willReturn('julia');
        $tokenStorage->getToken()->willReturn($token);
        $this->beConstructedWith($publishJobToQueue, $tokenStorage);
    }

    function it_is_a_compute_transformations_job_launcher()
    {
        $this->shouldImplement(ComputeTransformationFromAssetFamilyIdentifierLauncherInterface::class);
    }

    function it_publishes_the_job(PublishJobToQueue $publishJobToQueue)
    {
        $publishJobToQueue->publish(
            'asset_manager_compute_transformations', [
                'asset_family_identifier' => 'packshot'
            ],
            false,
            'julia'
        )->shouldBeCalledOnce();

        $this->launch(AssetFamilyIdentifier::fromString('packshot'));
    }
}
