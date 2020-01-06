<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class Periodicity
{
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';

    private const ALLOWED_VALUES = [
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
    ];

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (! in_array($value, self::ALLOWED_VALUES)) {
            throw new \InvalidArgumentException('Unknown periodicity value');
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
}
