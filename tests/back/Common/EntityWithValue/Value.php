<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue;

final class Value
{
    private const ALL_CHANNELS = 'all-channels';
    private const ALL_LOCALES = 'all-locales';

    /** @var Code */
    private $attribute;

    /** @var mixed */
    private $data;

    /** @var Code */
    private $locale;

    /** @var Code */
    private $channel;

    /**
     * @param Code  $attribute
     * @param mixed $data
     * @param Code  $locale
     * @param Code  $channel
     */
    private function __construct(Code $attribute, $data, Code $locale, Code $channel)
    {
        $this->attribute = $attribute;
        $this->data = $data;
        $this->locale = $locale;
        $this->channel = $channel;
    }

    /**
     * @param string $attribute
     * @param mixed  $data
     *
     * @return Value
     */
    public function create(string $attribute, $data): Value
    {
        return new self(
            Code::fromString($attribute),
            $data,
            Code::fromString(self::ALL_LOCALES),
            Code::fromString(self::ALL_CHANNELS)
        );
    }

    /**
     * @param string $attribute
     * @param mixed  $data
     * @param string $channel
     *
     * @return Value
     */
    public static function withChannel(string $attribute, $data, string $channel): Value
    {
        return new self(
            Code::fromString($attribute),
            $data,
            Code::fromString(self::ALL_LOCALES),
            Code::fromString($channel)
        );
    }

    /**
     * @param string $attribute
     * @param mixed  $data
     * @param string $locale
     *
     * @return Value
     */
    public static function withLocale(string $attribute, $data, string $locale): Value
    {
        return new self(
            Code::fromString($attribute),
            $data,
            Code::fromString($locale),
            Code::fromString(self::ALL_CHANNELS)
        );
    }

    /**
     * @param string $attribute
     * @param mixed  $data
     * @param string $locale
     * @param string $channel
     *
     * @return Value
     */
    public static function withLocaleAndChannel(string $attribute, $data, string $locale, string $channel): Value
    {
        $locale = '' === $locale ? self::ALL_LOCALES : $locale;
        $channel = '' === $channel ? self::ALL_CHANNELS : $channel;

        return new self(
            Code::fromString($attribute),
            $data,
            Code::fromString($locale),
            Code::fromString($channel)
        );
    }

    /**
     * @return array
     */
    public function toStandardFormat(): array
    {
        return [
            'data' => $this->data,
            'locale' => self::ALL_LOCALES === (string) $this->locale ? null : (string) $this->locale,
            'scope' => self::ALL_CHANNELS === (string) $this->channel ? null : (string) $this->channel,
        ];
    }
}
