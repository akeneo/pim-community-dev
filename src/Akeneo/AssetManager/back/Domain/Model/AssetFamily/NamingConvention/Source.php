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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;

class Source
{
    /** @var string */
    private $property;

    /** @var ChannelReference */
    private $channelReference;

    /** @var LocaleReference */
    private $localeReference;

    public function __construct(
        string $property,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ) {
        $this->property = $property;
        $this->channelReference = $channelReference;
        $this->localeReference = $localeReference;
    }

    public static function createFromNormalized($normalizedSource): self
    {
        return new self(
            $normalizedSource['property'],
            ChannelReference::createfromNormalized($normalizedSource['channel'] ?? null),
            LocaleReference::createfromNormalized($normalizedSource['locale'] ?? null)
        );
    }

    public function normalize(): array
    {
        return [
            'property' => $this->property,
            'channel' => $this->channelReference->normalize(),
            'locale' => $this->localeReference->normalize()
        ];
    }
}
