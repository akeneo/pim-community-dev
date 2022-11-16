<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Webmozart\Assert\Assert;

/**
 * Property to add an auto number to the structure
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AutoNumber implements PropertyInterface
{
    public const LIMIT_DIGITS_MIN = 1;
    public const LIMIT_DIGITS_MAX = 15;

    public function __construct(
        private int $numberMin,
        private int $digitsMin,
    ) {
    }

    public static function type(): string
    {
        return 'auto_number';
    }

    /**
     * @inheritDoc
     */
    public static function fromNormalized(array $normalizedProperty): PropertyInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::same($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'numberMin');
        Assert::greaterThanEq($normalizedProperty['numberMin'], 0);
        Assert::keyExists($normalizedProperty, 'digitsMin');

        return self::fromValues(intval($normalizedProperty['numberMin']), intval($normalizedProperty['digitsMin']));
    }

    public static function fromValues(int $numberMin, int $digitsMin): self
    {
        Assert::greaterThanEq($numberMin, 0);
        Assert::greaterThanEq($digitsMin, self::LIMIT_DIGITS_MIN);
        Assert::lessThanEq($digitsMin, self::LIMIT_DIGITS_MAX);

        return new self($numberMin, $digitsMin);
    }

    /**
     * @inheritDoc
     */
    public function normalize(): array
    {
        return [
            'type' => $this->type(),
            'numberMin' => $this->numberMin,
            'digitsMin' => $this->digitsMin,
        ];
    }

    public function numberMin(): int
    {
        return $this->numberMin;
    }

    public function digitsMin(): int
    {
        return $this->digitsMin;
    }
}
