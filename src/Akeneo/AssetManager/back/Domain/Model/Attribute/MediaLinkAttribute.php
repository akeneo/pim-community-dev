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

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkAttribute extends AbstractAttribute
{
    public const ATTRIBUTE_TYPE = 'media_link';

    private Prefix $prefix;

    private Suffix $suffix;

    private MediaType $mediaType;

    private function __construct(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        Prefix $prefix,
        Suffix $suffix,
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

        $this->prefix = $prefix;
        $this->suffix = $suffix;
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
        Prefix $prefix,
        Suffix $suffix,
        MediaType $mediaType
    ) {
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
            $prefix,
            $suffix,
            $mediaType
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'media_type' => $this->mediaType->normalize(),
                'prefix' => $this->prefix->normalize(),
                'suffix' => $this->suffix->normalize(),
            ]
        );
    }

    public function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }

    public function setPrefix(Prefix $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function setSuffix(Suffix $suffix): void
    {
        $this->suffix = $suffix;
    }

    public function setMediaType(MediaType $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getPrefix(): Prefix
    {
        return $this->prefix;
    }

    public function getSuffix(): Suffix
    {
        return $this->suffix;
    }

    public function getMediaType(): MediaType
    {
        return $this->mediaType;
    }
}
