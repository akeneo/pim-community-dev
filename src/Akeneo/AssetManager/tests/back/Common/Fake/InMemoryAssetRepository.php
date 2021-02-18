<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Event\DomainEvent;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAssetRepository implements AssetRepositoryInterface
{
    /** @var Asset[] */
    protected $assets = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Asset $asset): void
    {
        if (isset($this->assets[$asset->getIdentifier()->__toString()])) {
            throw new \RuntimeException('Asset already exists');
        }

        try {
            $this->getByAssetFamilyAndCode($asset->getAssetFamilyIdentifier(), $asset->getCode());
        } catch (AssetNotFoundException $exception) {
            $this->assets[$asset->getIdentifier()->__toString()] = $asset;

            return;
        } finally {
            $this->dispatchAssetEvents($asset);
        }

        throw new \RuntimeException('Asset already exists');
    }

    public function update(Asset $asset): void
    {
        if (!isset($this->assets[$asset->getIdentifier()->__toString()])) {
            throw new \RuntimeException('Expected to update one asset, but none was saved');
        }

        $this->assets[$asset->getIdentifier()->__toString()] = $asset;

        $this->dispatchAssetEvents($asset);
    }

    public function getByIdentifier(AssetIdentifier $identifier): Asset
    {
        if (!isset($this->assets[$identifier->__toString()])) {
            throw AssetNotFoundException::withIdentifier($identifier);
        }

        return $this->assets[$identifier->__toString()];
    }

    public function getByAssetFamilyAndCode(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code
    ): Asset {
        foreach ($this->assets as $asset) {
            if ($asset->getCode()->equals($code) && $asset->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)) {
                return $asset;
            }
        }

        throw AssetNotFoundException::withAssetFamilyAndCode($assetFamilyIdentifier, $code);
    }

    public function getByAssetFamilyAndCodes(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $assetCodes
    ): array {
        $assetsFound = [];

        foreach ($this->assets as $asset) {
            foreach ($assetCodes as $assetCode) {
                if ($asset->getCode()->equals(AssetCode::fromString($assetCode)) && $asset->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)) {
                    $assetsFound[] = $asset;
                }
            }
        }

        return $assetsFound;
    }

    public function deleteByAssetFamilyAndCode(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code
    ): void {
        foreach ($this->assets as $index => $asset) {
            if ($asset->getCode()->equals($code) && $asset->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)) {
                unset($this->assets[$index]);

                return;
            }
        }

        throw AssetNotFoundException::withAssetFamilyAndCode($assetFamilyIdentifier, $code);
    }

    public function deleteByAssetFamilyAndCodes(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): void
    {
        foreach ($assetCodes as $assetCode) {
            $this->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
        }
    }

    public function count(): int
    {
        return count($this->assets);
    }

    public function nextIdentifier(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code
    ): AssetIdentifier {
        return AssetIdentifier::create(
            $assetFamilyIdentifier->__toString(),
            $code->__toString(),
            Uuid::uuid4()->toString()
        );
    }

    public function hasAsset(AssetIdentifier $identifier)
    {
        return isset($this->assets[$identifier->__toString()]);
    }

    public function assetFamilyHasAssets(AssetFamilyIdentifier $assetFamilyIdentifier)
    {
        foreach ($this->assets as $asset) {
            if ($asset->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Asset[]
     */
    public function all(): array
    {
        return $this->assets;
    }

    public function countByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int
    {
        $count = 0;
        foreach ($this->assets as $asset) {
            if ($asset->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)) {
                $count++;
            }
        }

        return $count;
    }

    private function dispatchAssetEvents(Asset $asset)
    {
        foreach ($asset->getRecordedEvents() as $event) {
            if (!$event instanceof DomainEvent) {
                continue;
            }

            $this->eventDispatcher->dispatch($event, get_class($event));
        }

        $asset->clearRecordedEvents();
    }
}
