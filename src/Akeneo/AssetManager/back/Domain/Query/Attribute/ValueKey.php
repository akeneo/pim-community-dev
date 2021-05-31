<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Attribute;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Webmozart\Assert\Assert;

/**
 * Each Asset value is identified by a single key, generated from
 *  - Attribute identifier
 *  - Channel
 *  - Locale
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ValueKey
{
    private string $key;

    private function __construct(string $key)
    {
        Assert::notEmpty($key, 'Key should not be empty');

        $this->key = $key;
    }

    public static function create(
        AttributeIdentifier $attributeIdentifier,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): self {
        $channelPart = $channelReference->isEmpty() ? '' : sprintf('_%s', $channelReference->normalize());
        $localePart = $localeReference->isEmpty() ? '' : sprintf('_%s', $localeReference->normalize());
        $key = sprintf('%s%s%s', $attributeIdentifier->normalize(), $channelPart, $localePart);

        return new self($key);
    }

    public static function createFromNormalized(string $normalizedKey): self
    {
        return new self($normalizedKey);
    }

    public function __toString(): string
    {
        return $this->key;
    }
}
