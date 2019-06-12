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

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Suffix;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class UrlAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'url';

    /** @var Prefix  */
    private $prefix;

    /** @var Suffix  */
    private $suffix;

    /** @var MediaType  */
    private $mediaType;

    private function __construct(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        Prefix $prefix,
        Suffix $suffix,
        MediaType $mediaType
    ) {
        parent::__construct(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->mediaType = $mediaType;
    }

    public static function create(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        Prefix $prefix,
        Suffix $suffix,
        MediaType $mediaType
    ) {
        return new self(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
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
