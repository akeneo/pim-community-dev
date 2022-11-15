<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractValue implements Value
{
    public const SEPARATOR = '|';

    public function __construct(
        protected AttributeUuid $uuid,
        protected AttributeCode $code,
        protected ?ValueLocale $locale,
        protected ?ValueChannel $channel,
    ){
    }

    public function getUuid(): AttributeUuid
    {
        return $this->uuid;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function getLocale(): ?ValueLocale
    {
        return $this->locale;
    }

    public function getChannel(): ?ValueChannel
    {
        return $this->channel;
    }

    public function getKey(): string
    {
        return sprintf(
            '%s'.self::SEPARATOR.'%s',
            $this->code,
            $this->uuid
        );
    }

    public function getKeyWithLocaleAndChannel(): string
    {
        return sprintf(
            '%s%s%s%s',
            $this->code,
            self::SEPARATOR.$this->uuid,
            !empty($this->channel) ? self::SEPARATOR.$this->channel : '',
            !empty($this->locale) ? self::SEPARATOR.$this->locale : '',
        );
    }

    /**
     * @return array{
     *     channel: string,
     *     locale: string,
     *     attribute_code: string,
     * }
     */
    public function normalize(): array
    {
        return [
            'channel' => !empty($this->channel) ? (string) $this->channel : null,
            'locale' => !empty($this->locale) ? (string) $this->locale : null,
            'attribute_code' => $this->getKey(),
        ];
    }
}
