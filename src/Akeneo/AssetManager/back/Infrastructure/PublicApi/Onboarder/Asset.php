<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder;

/**
 * @author    Quentin Favrie <quentin.favrie@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class Asset
{
    /** @var string */
    private $identifier;

    /** @var array */
    private $labels;

    /** @var string */
    private $code;

    /** @var string */
    private $assetFamilyIdentifier;

    /** @var array */
    private $media;

    /** @var string */
    private $attributeType;

    /** @var string */
    private $mediaType;

    public function __construct(
        string $identifier,
        array $labels,
        string $code,
        string $assetFamilyIdentifier,
        array $media,
        string $attributeType,
        string $mediaType
    ) {
        $this->identifier = $identifier;
        $this->labels = $labels;
        $this->code = $code;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->media = $media;
        $this->attributeType = $attributeType;
        $this->mediaType = $mediaType;
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
