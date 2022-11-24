<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ImageData from ImageDataValue
 */
abstract class AbstractValue implements Value
{
    public const SEPARATOR = '|';
    public const IMAGE_TYPE = 'image';
    public const TEXT_TYPE = 'text';
    public const TEXT_AREA_TYPE = 'textarea';

    public function __construct(
        protected AttributeUuid $uuid,
        protected AttributeCode $code,
        protected ?ChannelValue $channel,
        protected ?LocaleValue $locale,
    ) {
    }

    public function getUuid(): AttributeUuid
    {
        return $this->uuid;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function getLocale(): ?LocaleValue
    {
        return $this->locale;
    }

    public function getChannel(): ?ChannelValue
    {
        return $this->channel;
    }

    public function getKey(): string
    {
        return sprintf(
            '%s'.self::SEPARATOR.'%s',
            $this->code,
            $this->uuid,
        );
    }

    public function getKeyWithChannelAndLocale(): string
    {
        return sprintf(
            '%s%s%s',
            $this->getKey(),
            !empty($this->channel) ? self::SEPARATOR.$this->channel : '',
            !empty($this->locale) ? self::SEPARATOR.$this->locale : '',
        );
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function normalize(): array
    {
        return [
            $this->getKeyWithChannelAndLocale() => [
                'channel' => !empty($this->channel) ? (string) $this->channel : null,
                'locale' => !empty($this->locale) ? (string) $this->locale : null,
                'attribute_code' => $this->getKey(),
            ],
        ];
    }

    /**
     * @param  array{
     *     data: mixed,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * } $value
     */
    public static function fromType(array $value): Value
    {
        $type = $value['type'];

        return match ($type) {
            self::TEXT_TYPE => TextValue::fromArray($value),
            self::TEXT_AREA_TYPE => TextAreaValue::fromArray($value),
            self::IMAGE_TYPE => ImageValue::fromArray($value),
            default => throw new \LogicException(sprintf('Type not recognized: "%s"', $type)),
        };
    }
}
