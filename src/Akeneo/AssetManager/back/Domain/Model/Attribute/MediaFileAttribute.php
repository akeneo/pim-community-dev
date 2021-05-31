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

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFileAttribute extends AbstractAttribute
{
    public const ATTRIBUTE_TYPE = 'media_file';

    private AttributeMaxFileSize $maxFileSize;

    private AttributeAllowedExtensions $allowedExtensions;

    private MediaType $mediaType;

    protected function __construct(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxFileSize $maxFileSize,
        AttributeAllowedExtensions $extensions,
        MediaType $mediaType
    ) {
        parent::__construct(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $isReadOnly,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->maxFileSize = $maxFileSize;
        $this->allowedExtensions = $extensions;
        $this->mediaType = $mediaType;
    }

    public static function create(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxFileSize $maxFileSize,
        AttributeAllowedExtensions $extensions,
        MediaType $mediaType
    ): self {
        return new self(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $isReadOnly,
            $valuePerChannel,
            $valuePerLocale,
            $maxFileSize,
            $extensions,
            $mediaType
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'max_file_size' => $this->maxFileSize->normalize(),
                'allowed_extensions' => $this->allowedExtensions->normalize(),
                'media_type' => $this->mediaType->normalize()
            ]
        );
    }

    public function hasMaxFileSizeLimit():bool
    {
        return $this->maxFileSize->hasLimit();
    }

    public function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }

    public function setMaxFileSize(AttributeMaxFileSize $newMaxFileSize): void
    {
        $this->maxFileSize = $newMaxFileSize;
    }

    public function setAllowedExtensions(AttributeAllowedExtensions $newAllowedExtensions): void
    {
        $this->allowedExtensions = $newAllowedExtensions;
    }

    public function setMediaType(MediaType $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getMaxFileSize(): AttributeMaxFileSize
    {
        return $this->maxFileSize;
    }

    public function getAllowedExtensions(): AttributeAllowedExtensions
    {
        return $this->allowedExtensions;
    }

    public function getMediaType(): MediaType
    {
        return $this->mediaType;
    }
}
