<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TimePeriod
{
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';

    private const ALLOWED_VALUES = [
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
        self::YEARLY,
    ];

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (! in_array($value, self::ALLOWED_VALUES)) {
            throw new \InvalidArgumentException('Unknown time period value');
        }

        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public static function daily(): self
    {
        return new self(self::DAILY);
    }

    public static function weekly(): self
    {
        return new self(self::WEEKLY);
    }

    public static function monthly(): self
    {
        return new self(self::MONTHLY);
    }

    public static function yearly(): self
    {
        return new self(self::YEARLY);
    }
}
