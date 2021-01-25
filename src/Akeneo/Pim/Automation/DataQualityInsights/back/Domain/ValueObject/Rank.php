<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Rank implements \JsonSerializable
{
    public const LETTERS_MAPPING = [
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        5 => 'E',
    ];

    /** @var int */
    private $value;

    /** @var string */
    private $code;

    private function __construct(int $value, string $code)
    {
        if (0 !== strpos($code, 'rank_')) {
            throw new \InvalidArgumentException(sprintf('The rank code "%s" is invalid', $code));
        }

        if ($value < 1 || $value > 5) {
            throw new \InvalidArgumentException(sprintf('A rank must be between 1 and 5 ("%d" given)', $value));
        }

        $this->value = $value;
        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        $value = intval(str_replace('rank_', '', $code));

        return new self($value, $code);
    }

    public static function fromInt(int $value): self
    {
        return new self($value, sprintf('rank_%d', $value));
    }

    public static function fromLetter(string $letter): self
    {
        $ranksByLetter = array_flip(self::LETTERS_MAPPING);

        if (!isset($ranksByLetter[$letter])) {
            throw new \InvalidArgumentException(sprintf('The letter "%s" does not correspond to any rank.', $letter));
        }

        return self::fromInt($ranksByLetter[$letter]);
    }

    public static function fromRate(Rate $rate): self
    {
        $rate = $rate->toInt();

        switch (true) {
            case ($rate >= 90):
                return self::fromInt(1);
            case ($rate >= 80):
                return self::fromInt(2);
            case ($rate >= 70):
                return self::fromInt(3);
            case ($rate >= 60):
                return self::fromInt(4);
            default:
                return self::fromInt(5);
        }
    }

    public function __toString()
    {
        return $this->code;
    }

    public function jsonSerialize()
    {
        return $this->code;
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toLetter(): string
    {
        return self::LETTERS_MAPPING[$this->value];
    }
}
