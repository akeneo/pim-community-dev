<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Rate
{
    /** @var int */
    private $rate;

    public function __construct(int $rate)
    {
        if ($rate < 0 || $rate > 100) {
            throw new \InvalidArgumentException('A rate must be an integer between 0 and 100');
        }

        $this->rate = $rate;
    }

    public function toInt(): int
    {
        return $this->rate;
    }

    public function isPerfect(): bool
    {
        return $this->rate === 100;
    }

    /**
     * @deprecated
     */
    public function __toString()
    {
        return $this->toLetter();
    }

    public function toLetter()
    {
        return Rank::fromRate($this)->toLetter();
    }
}
