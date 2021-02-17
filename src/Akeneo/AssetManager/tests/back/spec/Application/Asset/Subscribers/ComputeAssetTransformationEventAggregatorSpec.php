<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetIdentifiersLauncherInterface;
use Akeneo\AssetManager\Application\Asset\Subscribers\ComputeAssetTransformationEventAggregator;
use Akeneo\AssetManager\Application\AssetFamily\Transformation\GetOutdatedVariationSourceInterface;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ComputeAssetTransformationEventAggregatorSpec extends ObjectBehavior
{
    function let(
        ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        GetOutdatedVariationSourceInterface $getOutdatedVariationSource
    ) {
        $this->beConstructedWith(
            $computeTransformationLauncher,
            $assetRepository,
            $assetFamilyRepository,
            $getOutdatedVariationSource
        );
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ComputeAssetTransformationEventAggregator::class);
    }

    function it_is_a_subscriber()
    {
        $this->beAnInstanceOf(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [
                AssetUpdatedEvent::class => 'whenAssetCreatedOrUpdated',
                AssetCreatedEvent::class => 'whenAssetCreatedOrUpdated',
            ]
        );
    }

    function it_launches_a_compute_transformation_job_on_asset_create_or_update(
        ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher,
        GetOutdatedVariationSourceInterface $getOutdatedVariationSource,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        TransformationCollection $transformationCollection,
        \ArrayIterator $transformationCollectionIterator,
        Transformation $transformation,
        FileData $fileData
    ) {
        $createdAssetIdentifier = AssetIdentifier::fromString('created_id');
        $createdAssetEvent = new AssetUpdatedEvent(
            $createdAssetIdentifier,
            AssetCode::fromString('created_code'),
            AssetFamilyIdentifier::fromString('family')
        );
        $updatedAssetIdentifier = AssetIdentifier::fromString('updated_id');
        $updatedAssetEvent = new AssetUpdatedEvent(
            $updatedAssetIdentifier,
            AssetCode::fromString('updated_code'),
            AssetFamilyIdentifier::fromString('family')
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $createdAsset = $this->getAsset($updatedAssetIdentifier, $assetFamilyIdentifier);
        $assetRepository->getByIdentifier($createdAssetIdentifier)->willReturn($createdAsset);
        $updatedAsset = $this->getAsset($updatedAssetIdentifier, $assetFamilyIdentifier);
        $assetRepository->getByIdentifier($updatedAssetIdentifier)->willReturn($updatedAsset);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformationCollection);

        $transformationCollection->getIterator()->willReturn($transformationCollectionIterator);
        $transformationCollectionIterator->valid()->willReturn(true, true, false);
        $transformationCollectionIterator->current()->willReturn($transformation);
        $transformationCollectionIterator->rewind()->shouldBeCalled();
        $getOutdatedVariationSource->forAssetAndTransformation($createdAsset, $transformation)
            ->willReturn($fileData);
        $getOutdatedVariationSource->forAssetAndTransformation($updatedAsset, $transformation)
            ->willReturn($fileData);

        $this->whenAssetCreatedOrUpdated($createdAssetEvent);
        $this->whenAssetCreatedOrUpdated($updatedAssetEvent);
        $this->whenAssetCreatedOrUpdated($createdAssetEvent);

        $computeTransformationLauncher->launch([
            'created_id' => $createdAssetIdentifier,
            'updated_id' => $updatedAssetIdentifier,
        ])->shouldBeCalled();
        $this->flushEvents();
    }

    function it_does_not_launch_job_at_update_if_all_asset_values_are_up_to_date(
        ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher,
        GetOutdatedVariationSourceInterface $getOutdatedVariationSource,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        TransformationCollection $transformationCollection,
        \ArrayIterator $transformationCollectionIterator,
        Transformation $transformation1,
        Transformation $transformation2
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

        $transformationCollection->getIterator()->willReturn($transformationCollectionIterator);
        $transformationCollectionIterator->valid()->willReturn(true, true, false);
        $transformationCollectionIterator->current()->willReturn($transformation1, $transformation2);
        $transformationCollectionIterator->rewind()->shouldBeCalled();
        $transformationCollectionIterator->next()->shouldBeCalled();
        $getOutdatedVariationSource->forAssetAndTransformation($asset, $transformation1)
            ->willReturn(null);
        $getOutdatedVariationSource->forAssetAndTransformation($asset, $transformation2)
            ->willReturn(null);

        $computeTransformationLauncher->launch([$assetIdentifier])->shouldNotBeCalled();

        $this->whenAssetCreatedOrUpdated($assetUpdatedEvent);
    }

    private function getAsset(AssetIdentifier $assetIdentifier, AssetFamilyIdentifier $assetFamilyIdentifier): Asset
    {
        $assetCode = AssetCode::fromString('code');

        return Asset::create($assetIdentifier, $assetFamilyIdentifier, $assetCode, ValueCollection::fromValues([]));
    }
}
