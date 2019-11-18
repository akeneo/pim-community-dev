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

class Source
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
        //TODO: see if we have to add explicit error message
        Assert::isInstanceOf($attribute, ImageAttribute::class);
        $attribute->hasValuePerChannel() ? Assert::false($channelReference->isEmpty()) : Assert::true($channelReference->isEmpty());
        $attribute->hasValuePerLocale() ? Assert::false($localeReference->isEmpty()) : Assert::true($localeReference->isEmpty());

        return new self($attribute->getIdentifier(), $channelReference, $localeReference);
    }

    public function getAttributeIdentifierAsString(): string
    {
        return $this->attributeIdentifier->stringValue();
    }

    public function getChannelReference(): ChannelReference
    {
        return $this->channelReference;
    }

    public function getLocaleReference(): LocaleReference
    {
        return $this->localeReference;
    }
}
