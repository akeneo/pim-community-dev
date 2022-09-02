<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder;

/**
 * @author    Quentin Favrie <quentin.favrie@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class Asset
{
    public function __construct(
        private string $identifier,
        private array $labels,
        private string $code,
        private string $assetFamilyIdentifier,
        private array $media,
        private string $attributeType,
        private string $mediaType
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAssetFamilyIdentifier(): string
    {
        return $this->assetFamilyIdentifier;
    }

    public function getMedia(): array
    {
        return $this->media;
    }

    public function getAttributeType(): string
    {
        return $this->attributeType;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
