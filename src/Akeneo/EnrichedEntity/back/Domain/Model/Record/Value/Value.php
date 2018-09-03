<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\EnrichedEntity\back\Domain\Model\ChannelIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class Value
{
    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    /** @var ChannelIdentifier|null */
    private $channelIdentifier;

    /** @var LocaleIdentifier|null */
    private $localeIdentifier;

    /** @var ValueDataInterface */
    private $data;

    private function __construct(
        AttributeIdentifier $attributeIdentifier,
        ?ChannelIdentifier $channelIdentifier,
        ?LocaleIdentifier $localeIdentifier,
        ValueDataInterface $data
    ) {
        $this->attributeIdentifier = $attributeIdentifier;
        $this->channelIdentifier = $channelIdentifier;
        $this->localeIdentifier = $localeIdentifier;
        $this->data = $data;
    }

    public function isEmpty(): bool
    {
        return $this->data instanceof EmptyData;
    }

    public function hasChannel(): bool
    {
        return null !== $this->channelIdentifier;
    }

    public function hasLocale(): bool
    {
        return null !== $this->localeIdentifier;
    }

    public function normalize(): array
    {
        return [
            'attribute' => $this->attributeIdentifier->normalize(),
            'channel' => $this->channelIdentifier->normalize(),
            'locale' => $this->localeIdentifier->normalize(),
            'data' => $this->data->normalize()
        ];
    }

    public static function create(
        AttributeIdentifier $attributeIdentifier,
        ?ChannelIdentifier $channelIdentifier,
        ?LocaleIdentifier $localeIdentifier,
        ValueDataInterface $data
    ): Value {
        return new self($attributeIdentifier, $channelIdentifier, $localeIdentifier, $data);
    }
}
