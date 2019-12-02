<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationLauncherInterface;
use Akeneo\AssetManager\Application\Asset\Subscribers\ComputeAssetTransformationSubscriber;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ComputeAssetTransformationSubscriberSpec extends ObjectBehavior
{
    function let(ComputeTransformationLauncherInterface $computeTransformationLauncher)
    {
        $this->beConstructedWith($computeTransformationLauncher);
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ComputeAssetTransformationSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->beAnInstanceOf(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [
                AssetUpdatedEvent::class => 'whenAssetUpdated',
                AssetCreatedEvent::class => 'whenAssetCreated',
            ]
        );
    }

    function it_launches_a_compute_transformation_job_on_asset_update(
        ComputeTransformationLauncherInterface $computeTransformationLauncher
    ) {
        $assetIdentifier = AssetIdentifier::fromString('id');
        $assetUpdatedEvent = new AssetUpdatedEvent(
            $assetIdentifier,
            AssetCode::fromString('code'),
            AssetFamilyIdentifier::fromString('family')
        );

        $computeTransformationLauncher->launch([$assetIdentifier])->shouldBeCalled();

        $this->whenAssetUpdated($assetUpdatedEvent);
    }

    function it_launches_a_compute_transformation_job_on_asset_creation(
        ComputeTransformationLauncherInterface $computeTransformationLauncher
    ) {
        $assetIdentifier = AssetIdentifier::fromString('id');
        $assetCreatedEvent = new AssetCreatedEvent(
            $assetIdentifier,
            AssetCode::fromString('code'),
            AssetFamilyIdentifier::fromString('family')
        );

        $computeTransformationLauncher->launch([$assetIdentifier])->shouldBeCalled();

        $this->whenAssetCreated($assetCreatedEvent);
    }
}
