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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Webmozart\Assert\Assert;

class Source implements TransformationReference
{
    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    /** @var ChannelReference */
    private $channelReference;

    /** @var LocaleReference */
    private $localeReference;

    private function __construct(
        AttributeIdentifier $attributeIdentifier,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ) {
        $this->attributeIdentifier = $attributeIdentifier;
        $this->channelReference = $channelReference;
        $this->localeReference = $localeReference;
    }

    public static function create(
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): self {
        Assert::isInstanceOf($attribute, ImageAttribute::class);

        $attribute->hasValuePerChannel() ?
            Assert::false(
                $channelReference->isEmpty(),
                sprintf('Attribute "%s" is scopable, you must define a channel', $attribute->getIdentifier()->stringValue())
            ) :
            Assert::true(
                $channelReference->isEmpty(),
                sprintf('Attribute "%s" is not scopable, you cannot define a channel', $attribute->getIdentifier()->stringValue())
            );

        $attribute->hasValuePerLocale() ?
            Assert::false(
                $localeReference->isEmpty(),
                sprintf('Attribute "%s" is localizable, you must define a locale', $attribute->getIdentifier()->stringValue())
            ) :
            Assert::true(
                $localeReference->isEmpty(),
                sprintf('Attribute "%s" is not localizable, you cannot define a locale', $attribute->getIdentifier()->stringValue())
            );

        return new self($attribute->getIdentifier(), $channelReference, $localeReference);
    }

    public function getAttributeIdentifier(): AttributeIdentifier
    {
        return $this->attributeIdentifier;
    }

    public function getChannelReference(): ChannelReference
    {
        return $this->channelReference;
    }

    public function getLocaleReference(): LocaleReference
    {
        return $this->localeReference;
    }

    public function equals(TransformationReference $reference): bool
    {
        return
            $this->getAttributeIdentifier()->equals($reference->getAttributeIdentifier()) &&
            $this->getChannelReference()->equals($reference->getChannelReference()) &&
            $this->getLocaleReference()->equals($reference->getLocaleReference());
    }

    public function normalize(): array
    {
        return [
            'attribute' => $this->attributeIdentifier->normalize(),
            'channel' => $this->channelReference->normalize(),
            'locale' => $this->localeReference->normalize(),
        ];
    }
}
