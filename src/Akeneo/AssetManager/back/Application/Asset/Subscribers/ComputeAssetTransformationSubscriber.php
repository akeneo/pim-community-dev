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

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationLauncherInterface;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetOutdatedValues;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ComputeAssetTransformationSubscriber implements EventSubscriberInterface
{
    /** @var ComputeTransformationLauncherInterface */
    private $computeTransformationLauncher;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var GetOutdatedValues */
    private $getOutdatedValues;

    public function __construct(
        ComputeTransformationLauncherInterface $computeTransformationLauncher,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        GetOutdatedValues $getOutdatedValues
    ) {
        $this->computeTransformationLauncher = $computeTransformationLauncher;
        $this->getOutdatedValues = $getOutdatedValues;
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetUpdatedEvent::class => 'whenAssetUpdated',
            AssetCreatedEvent::class => 'whenAssetCreated',
        ];
    }

    public function whenAssetUpdated(AssetUpdatedEvent $assetUpdatedEvent): void
    {
        $this->launchJobIfNeeded($assetUpdatedEvent->getAssetIdentifier());
    }

    public function whenAssetCreated(AssetCreatedEvent $assetCreatedEvent): void
    {
        $this->launchJobIfNeeded($assetCreatedEvent->getAssetIdentifier());
    }

    protected function launchJobIfNeeded(AssetIdentifier $assetIdentifier): void
    {
        try {
            $asset = $this->assetRepository->getByIdentifier($assetIdentifier);
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($asset->getAssetFamilyIdentifier());
            $transformationCollection = $assetFamily->getTransformationCollection();
            if (0 === $transformationCollection->count()) {
                return;
            }

            $valueCollection = $this->getOutdatedValues->fromAsset($asset, $transformationCollection);
            if (0 === $valueCollection->count()) {
                return;
            }
        } catch (AssetNotFoundException | AssetFamilyNotFoundException | \LogicException $e) {
            // Here we catch all errors if the asset is not found, the asset family is not found or
            // one attribute in transformation is not found.
            return;
        }

        $this->computeTransformationLauncher->launch([$assetIdentifier]);
    }
}
