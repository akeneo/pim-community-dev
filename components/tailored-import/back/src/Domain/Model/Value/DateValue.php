<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model\Value;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateValue implements ValueInterface
{
    private const TYPE = 'date';

    public function __construct(
        private \DateTimeImmutable $value,
    ) {
    }

    public function getValue(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'value' => $this->value->format('Y-m-d'),
        ];
    }
}
