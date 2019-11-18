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

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Webmozart\Assert\Assert;

final class Target
{
    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    /** @var ChannelIdentifier|null */
    private $channelIdentifier;

    /** @var LocaleIdentifier|null */
    private $localeIdentifier;

    private function __construct(
        AttributeIdentifier $attributeIdentifier,
        ?ChannelIdentifier $channelIdentifier,
        ?LocaleIdentifier $localeIdentifier
    ) {
        $this->attributeIdentifier = $attributeIdentifier;
        $this->channelIdentifier = $channelIdentifier;
        $this->localeIdentifier = $localeIdentifier;
    }

    public static function create(
        AbstractAttribute $attribute,
        ?ChannelIdentifier $channelIdentifier,
        ?LocaleIdentifier $localeIdentifier
    ): self {
        //TODO: see if we have to add explicit error message
        Assert::isInstanceOf($attribute, ImageAttribute::class);
        $attribute->hasValuePerChannel() ? Assert::notNull($channelIdentifier) : Assert::null($channelIdentifier);
        $attribute->hasValuePerLocale() ? Assert::notNull($localeIdentifier) : Assert::null($localeIdentifier);

        return new self($attribute->getIdentifier(), $channelIdentifier, $localeIdentifier);
    }
}
