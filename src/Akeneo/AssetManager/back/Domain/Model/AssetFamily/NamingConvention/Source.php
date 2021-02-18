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
use Webmozart\Assert\Assert;

class Source
{
    private const ASSET_CODE_PROPERTY = 'code';

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

    public static function createFromNormalized(array $normalizedSource): self
    {
        Assert::keyExists($normalizedSource, 'property');
        Assert::stringNotEmpty($normalizedSource['property']);

        return new self(
            $normalizedSource['property'],
            ChannelReference::createFromNormalized($normalizedSource['channel'] ?? null),
            LocaleReference::createFromNormalized($normalizedSource['locale'] ?? null)
        );
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getChannelReference(): ChannelReference
    {
        return $this->channelReference;
    }

    public function getLocaleReference(): LocaleReference
    {
        return $this->localeReference;
    }

    public function normalize(): array
    {
        return [
            'property' => $this->property,
            'channel' => $this->channelReference->normalize(),
            'locale' => $this->localeReference->normalize(),
        ];
    }

    public function isAssetCode(): bool
    {
        return self::ASSET_CODE_PROPERTY === $this->property;
    }
}
