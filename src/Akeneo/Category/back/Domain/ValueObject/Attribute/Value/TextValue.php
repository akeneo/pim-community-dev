<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TextValue extends AbstractValue
{
    public function __construct(
        private readonly string $value,
        AttributeUuid $uuid,
        AttributeCode $code,
        ?ValueLocale $locale,
        ?ValueChannel $channel,
    ) {
        Assert::stringNotEmpty($value);

        parent::__construct(
            uuid: $uuid,
            code: $code,
            locale: $locale,
            channel: $channel
        );
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array{
     *     data: string,
     *     channel: string,
     *     locale: string,
     *     attribute_code: string,
     * }
     */
    public function normalize(): array
    {
        return array_merge(
            ['data' => $this->value],
            parent::normalize()
        );
    }
}
