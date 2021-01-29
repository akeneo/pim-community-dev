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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Webmozart\Assert\Assert;

class Target implements TransformationReference
{
    /** @var AttributeCode */
    private $attributeCode;

    /** @var ChannelReference */
    private $channelReference;

    /** @var LocaleReference */
    private $localeReference;

    private function __construct(
        AttributeCode $attributeCode,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ) {
        $this->attributeCode = $attributeCode;
        $this->channelReference = $channelReference;
        $this->localeReference = $localeReference;
    }

    public static function create(
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): self {
        Assert::isInstanceOf($attribute, MediaFileAttribute::class);

        $attribute->hasValuePerChannel() ?
            Assert::false(
                $channelReference->isEmpty(),
                sprintf('Attribute "%s" is scopable, you must define a channel', (string) $attribute->getCode())
            ) :
            Assert::true(
                $channelReference->isEmpty(),
                sprintf('Attribute "%s" is not scopable, you cannot define a channel', (string) $attribute->getCode())
            );

        $attribute->hasValuePerLocale() ?
            Assert::false(
                $localeReference->isEmpty(),
                sprintf('Attribute "%s" is localizable, you must define a locale', (string) $attribute->getCode())
            ) :
            Assert::true(
                $localeReference->isEmpty(),
                sprintf('Attribute "%s" is not localizable, you cannot define a locale', (string) $attribute->getCode())
            );

        return new self($attribute->getCode(), $channelReference, $localeReference);
    }

    public static function createFromNormalized(array $normalizedTarget): self
    {
        Assert::keyExists($normalizedTarget, 'attribute');
        Assert::keyExists($normalizedTarget, 'channel');
        Assert::keyExists($normalizedTarget, 'locale');

        return new self(
            AttributeCode::fromString($normalizedTarget['attribute']),
            ChannelReference::createFromNormalized($normalizedTarget['channel']),
            LocaleReference::createFromNormalized($normalizedTarget['locale'])
        );
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
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
            $this->getAttributeCode()->equals($reference->getAttributeCode()) &&
            $this->getChannelReference()->equals($reference->getChannelReference()) &&
            $this->getLocaleReference()->equals($reference->getLocaleReference());
    }

    public function normalize(): array
    {
        return [
            'attribute' => (string) $this->attributeCode,
            'channel' => $this->channelReference->normalize(),
            'locale' => $this->localeReference->normalize(),
        ];
    }
}
