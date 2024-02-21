<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TextAreaValue extends AbstractValue
{
    private function __construct(
        private readonly ?string $value,
        AttributeUuid $uuid,
        AttributeCode $code,
        ?ChannelValue $channel,
        ?LocaleValue $locale,
    ) {
        parent::__construct(
            uuid: $uuid,
            code: $code,
            channel: $channel,
            locale: $locale,
        );
    }

    public static function fromApplier(?string $value, string $uuid, string $code, ?string $channel, ?string $locale): self
    {
        return new self(
            value: $value,
            uuid: AttributeUuid::fromString($uuid),
            code: new AttributeCode($code),
            channel: !empty($channel) ? new ChannelValue($channel) : null,
            locale: !empty($locale) ? new LocaleValue($locale) : null,
        );
    }

    /**
     * @param array{
     *     data: ?string,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * } $value
     */
    public static function fromArray(array $value): self
    {
        $identifiers = explode(AbstractValue::SEPARATOR, $value['attribute_code']);
        if (count($identifiers) !== 2) {
            throw new \InvalidArgumentException('Cannot find code and uuid.');
        }

        return new self(
            value: $value['data'],
            uuid: AttributeUuid::fromString($identifiers[1]),
            code: new AttributeCode($identifiers[0]),
            channel: !empty($value['channel']) ? new ChannelValue($value['channel']) : null,
            locale: !empty($value['locale']) ? new LocaleValue($value['locale']) : null,
        );
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return array<string, array{
     *     data: ?string,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * }>
     */
    public function normalize(): array
    {
        return array_merge_recursive(
            [$this->getKeyWithChannelAndLocale() => [
                'data' => $this->value,
                'type' => AbstractValue::TEXT_AREA_TYPE,
            ]],
            parent::normalize(),
        );
    }
}
