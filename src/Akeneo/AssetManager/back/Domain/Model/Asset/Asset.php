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

namespace Akeneo\AssetManager\Domain\Model\Asset;

use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Event\DomainEvent;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class Asset
{
    private AssetIdentifier $identifier;

    private AssetCode $code;

    private AssetFamilyIdentifier $assetFamilyIdentifier;

    private ValueCollection $valueCollection;

    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    private array $recordedEvents = [];

    private function __construct(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code,
        ValueCollection $valueCollection,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->identifier = $identifier;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->code = $code;
        $this->valueCollection = $valueCollection;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code,
        ValueCollection $valueCollection
    ): self {
        $asset = new self(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $valueCollection,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );

        $asset->recordedEvents[AssetCreatedEvent::class] = new AssetCreatedEvent(
            $identifier,
            $code,
            $assetFamilyIdentifier
        );

        return $asset;
    }

    public static function fromState(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code,
        ValueCollection $valueCollection,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        return new self($identifier, $assetFamilyIdentifier, $code, $valueCollection, $createdAt, $updatedAt);
    }

    public function getIdentifier(): AssetIdentifier
    {
        return $this->identifier;
    }

    public function getCode(): AssetCode
    {
        return $this->code;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    public function equals(Asset $asset): bool
    {
        return $this->identifier->equals($asset->identifier);
    }

    public function getValues(): ValueCollection
    {
        return $this->valueCollection;
    }

    public function setValue(Value $value): void
    {
        if ($this->valueCollection->hasValue($value)) {
            return;
        }

        $this->valueCollection = $this->valueCollection->setValue($value);
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->recordedEvents[AssetUpdatedEvent::class] = new AssetUpdatedEvent(
            $this->identifier,
            $this->code,
            $this->assetFamilyIdentifier,
        );
    }

    public function findValue(ValueKey $valueKey): ?Value
    {
        return $this->valueCollection->findValue($valueKey);
    }

    public function normalize(): array
    {
        return [
            'identifier' => $this->identifier->normalize(),
            'code' => $this->code->normalize(),
            'assetFamilyIdentifier' => $this->assetFamilyIdentifier->normalize(),
            'values' => $this->valueCollection->normalize(),
        ];
    }

    public function filterValues(\Closure $closure): ValueCollection
    {
        return $this->valueCollection->filter($closure);
    }

    /**
     * @return DomainEvent[]
     */
    public function getRecordedEvents(): array
    {
        return array_values($this->recordedEvents);
    }

    public function clearRecordedEvents()
    {
        $this->recordedEvents = [];
    }
}
