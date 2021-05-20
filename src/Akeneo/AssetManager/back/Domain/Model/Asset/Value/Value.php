<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class Value
{
    private AttributeIdentifier $attributeIdentifier;

    private ChannelReference $channelReference;

    private LocaleReference $localeReference;

    private ValueDataInterface $data;

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

    public function getData(): ValueDataInterface
    {
        return $this->data;
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

    public function getValueKey(): ValueKey
    {
        return ValueKey::create($this->attributeIdentifier, $this->channelReference, $this->localeReference);
    }

    public function equals(Value $anotherValue): bool
    {
        return
            $anotherValue->getAttributeIdentifier()->equals($this->attributeIdentifier)
            && $this->sameAttribute($anotherValue)
            && $this->sameChannel($anotherValue)
            && $this->sameLocale($anotherValue)
            && $anotherValue->getData()->equals($this->getData());
    }
}
