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
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetOutdatedValues;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ComputeAssetTransformationSubscriberSpec extends ObjectBehavior
{
    function let(
        ComputeTransformationLauncherInterface $computeTransformationLauncher,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        GetOutdatedValues $getOutdatedValues
    ) {
        $this->beConstructedWith(
            $computeTransformationLauncher,
            $assetRepository,
            $assetFamilyRepository,
            $getOutdatedValues
        );
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
        ComputeTransformationLauncherInterface $computeTransformationLauncher,
        GetOutdatedValues $getOutdatedValues,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        TransformationCollection $transformationCollection,
        ValueCollection $valueCollection
    ) {
        $assetIdentifier = AssetIdentifier::fromString('id');
        $assetUpdatedEvent = new AssetUpdatedEvent(
            $assetIdentifier,
            AssetCode::fromString('code'),
            AssetFamilyIdentifier::fromString('family')
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $asset = $this->getAsset($assetIdentifier, $assetFamilyIdentifier);
        $assetRepository->getByIdentifier($assetIdentifier)->willReturn($asset);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformationCollection);

        $getOutdatedValues->fromAsset($asset, $transformationCollection)->willReturn($valueCollection);
        $valueCollection->count()->willReturn(1);

        $computeTransformationLauncher->launch([$assetIdentifier])->shouldBeCalled();

        $this->whenAssetUpdated($assetUpdatedEvent);
    }

    function it_does_not_launch_job_at_update_if_all_asset_values_are_up_to_date(
        ComputeTransformationLauncherInterface $computeTransformationLauncher,
        GetOutdatedValues $getOutdatedValues,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        TransformationCollection $transformationCollection,
        ValueCollection $valueCollection
    ) {
        $assetIdentifier = AssetIdentifier::fromString('id');
        $assetUpdatedEvent = new AssetUpdatedEvent(
            $assetIdentifier,
            AssetCode::fromString('code'),
            AssetFamilyIdentifier::fromString('family')
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $asset = $this->getAsset($assetIdentifier, $assetFamilyIdentifier);
        $assetRepository->getByIdentifier($assetIdentifier)->willReturn($asset);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformationCollection);

        $getOutdatedValues->fromAsset($asset, $transformationCollection)->willReturn($valueCollection);
        $valueCollection->count()->willReturn(0);

        $computeTransformationLauncher->launch([$assetIdentifier])->shouldNotBeCalled();

        $this->whenAssetUpdated($assetUpdatedEvent);
    }

    function it_launches_a_compute_transformation_job_on_asset_creation(
        ComputeTransformationLauncherInterface $computeTransformationLauncher,
        GetOutdatedValues $getOutdatedValues,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        TransformationCollection $transformationCollection,
        ValueCollection $valueCollection
    ) {
        $assetIdentifier = AssetIdentifier::fromString('id');
        $assetCreatedEvent = new AssetCreatedEvent(
            $assetIdentifier,
            AssetCode::fromString('code'),
            AssetFamilyIdentifier::fromString('family')
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $asset = $this->getAsset($assetIdentifier, $assetFamilyIdentifier);
        $assetRepository->getByIdentifier($assetIdentifier)->willReturn($asset);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformationCollection);

        $getOutdatedValues->fromAsset($asset, $transformationCollection)->willReturn($valueCollection);
        $valueCollection->count()->willReturn(1);

        $computeTransformationLauncher->launch([$assetIdentifier])->shouldBeCalled();

        $this->whenAssetCreated($assetCreatedEvent);
    }

    function it_does_not_launch_job_at_creation_if_all_asset_values_are_up_to_date(
        ComputeTransformationLauncherInterface $computeTransformationLauncher,
        GetOutdatedValues $getOutdatedValues,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        TransformationCollection $transformationCollection,
        ValueCollection $valueCollection
    ) {
        $assetIdentifier = AssetIdentifier::fromString('id');
        $assetCreatedEvent = new AssetCreatedEvent(
            $assetIdentifier,
            AssetCode::fromString('code'),
            AssetFamilyIdentifier::fromString('family')
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $asset = $this->getAsset($assetIdentifier, $assetFamilyIdentifier);
        $assetRepository->getByIdentifier($assetIdentifier)->willReturn($asset);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformationCollection);

        $getOutdatedValues->fromAsset($asset, $transformationCollection)->willReturn($valueCollection);
        $valueCollection->count()->willReturn(0);

        $computeTransformationLauncher->launch([$assetIdentifier])->shouldNotBeCalled();

        $this->whenAssetCreated($assetCreatedEvent);
    }

    private function getAsset(AssetIdentifier $assetIdentifier, AssetFamilyIdentifier $assetFamilyIdentifier): Asset
    {
        $assetCode = AssetCode::fromString('code');

        return Asset::create($assetIdentifier, $assetFamilyIdentifier, $assetCode, ValueCollection::fromValues([]));
    }
}
