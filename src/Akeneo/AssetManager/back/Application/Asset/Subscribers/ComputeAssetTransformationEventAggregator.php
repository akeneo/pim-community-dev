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

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetIdentifiersLauncherInterface;
use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\EventAggregatorInterface;
use Akeneo\AssetManager\Application\AssetFamily\Transformation\Exception\NonApplicableTransformationException;
use Akeneo\AssetManager\Application\AssetFamily\Transformation\GetOutdatedVariationSourceInterface;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class ComputeAssetTransformationEventAggregator implements EventAggregatorInterface, EventSubscriberInterface
{
    private const MAX_ASSET_BATCH = 200;

    /** @var ComputeTransformationFromAssetIdentifiersLauncherInterface */
    private $computeTransformationLauncher;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var GetOutdatedVariationSourceInterface */
    private $getOutdatedVariationSource;

    /** @var AssetIdentifier[] */
    private $assetsToComputeTransformations = [];

    public function __construct(
        ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        GetOutdatedVariationSourceInterface $getOutdatedVariationSource
    ) {
        $this->computeTransformationLauncher = $computeTransformationLauncher;
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->getOutdatedVariationSource = $getOutdatedVariationSource;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetUpdatedEvent::class => 'whenAssetCreatedOrUpdated',
            AssetCreatedEvent::class => 'whenAssetCreatedOrUpdated',
        ];
    }

    public function whenAssetCreatedOrUpdated(Event $assetEvent): void
    {
        Assert::isInstanceOfAny($assetEvent, [AssetCreatedEvent::class, AssetUpdatedEvent::class]);

        $assetIdentifier = $assetEvent->getAssetIdentifier();
        if (array_key_exists($assetIdentifier->normalize(), $this->assetsToComputeTransformations)) {
            return;
        }

        if (!$this->transformationsShouldBeComputedForAsset($assetIdentifier)) {
            return;
        }

        $this->assetsToComputeTransformations[$assetIdentifier->normalize()] = $assetIdentifier;
        if (count($this->assetsToComputeTransformations) === self::MAX_ASSET_BATCH) {
            $this->flushEvents();
        }
    }

    public function flushEvents(): void
    {
        if (empty($this->assetsToComputeTransformations)) {
            return;
        }

        $this->computeTransformationLauncher->launch($this->assetsToComputeTransformations);
        $this->assetsToComputeTransformations = [];
    }

    private function transformationsShouldBeComputedForAsset(AssetIdentifier $assetIdentifier): bool
    {
        try {
            $asset = $this->assetRepository->getByIdentifier($assetIdentifier);
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($asset->getAssetFamilyIdentifier());

            if (!$this->assetContainsOutdatedTransformation($assetFamily, $asset)) {
                return false;
            }
        } catch (AssetNotFoundException | AssetFamilyNotFoundException | AttributeNotFoundException $e) {
            // Here we catch all errors if the asset is not found, the asset family is not found or
            // one attribute in transformation is not found.
            return false;
        }

        return true;
    }

    private function assetContainsOutdatedTransformation(AssetFamily $assetFamily, Asset $asset): bool
    {
        foreach ($assetFamily->getTransformationCollection() as $transformation) {
            try {
                $source = $this->getOutdatedVariationSource->forAssetAndTransformation($asset, $transformation);
                if (null !== $source) {
                    return true;
                }
            } catch (NonApplicableTransformationException $e) {
            }
        }

        return false;
    }
}
