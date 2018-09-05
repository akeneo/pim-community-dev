<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class Value
{
    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    /** @var ChannelReference */
    private $channelReference;

    /** @var LocaleReference */
    private $localeReference;

    /** @var ValueDataInterface */
    private $data;

    private function __construct(
        AttributeIdentifier $attributeIdentifier,
        ChannelReference $channelReference,
        LocaleReference $localeReference,
        ValueDataInterface $data
    ) {
        $this->attributeIdentifier = $attributeIdentifier;
        $this->channelReference = $channelReference;
        $this->localeReference = $localeReference;
        $this->data = $data;
    }

    public static function create(
        AttributeIdentifier $attributeIdentifier,
        ChannelReference $channelReference,
        LocaleReference $localeReference,
        ValueDataInterface $data
    ): Value {
        return new self($attributeIdentifier, $channelReference, $localeReference, $data);
    }

    public function isEmpty(): bool
    {
        return $this->data instanceof EmptyData;
    }

    public function hasChannel(): bool
    {
        return !$this->channelReference->isEmpty();
    }

    public function hasLocale(): bool
    {
        return !$this->localeReference->isEmpty();
    }

    public function sameAttribute(Value $otherValue): bool
    {
        return $otherValue->attributeIdentifier->equals($this->attributeIdentifier);
    }

    public function sameChannel(Value $otherValue): bool
    {
        return $otherValue->channelReference->equals($this->channelReference);
    }

    public function sameLocale(Value $otherValue): bool
    {
        return $otherValue->localeReference->equals($this->localeReference);
    }

    public function normalize(): array
    {
        return [
            'attribute' => $this->attributeIdentifier->normalize(),
            'channel'   => $this->channelReference->normalize(),
            'locale'    => $this->localeReference->normalize(),
            'data'      => $this->data->normalize(),
        ];
    }
}
