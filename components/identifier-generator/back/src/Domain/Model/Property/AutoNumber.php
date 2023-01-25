<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * Property to add an auto number to the structure
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type AutoNumberNormalized array{type: string, numberMin: int, digitsMin: int}
 */
final class AutoNumber implements PropertyInterface
{
    public const LIMIT_NUMBER_MIN = 0;
    public const LIMIT_DIGITS_MIN = 1;
    public const LIMIT_DIGITS_MAX = 15;

    public function __construct(
        private readonly int $numberMin,
        private readonly int $digitsMin,
    ) {
        Assert::greaterThanEq($numberMin, self::LIMIT_NUMBER_MIN);
        Assert::greaterThanEq($digitsMin, self::LIMIT_DIGITS_MIN);
        Assert::lessThanEq($digitsMin, self::LIMIT_DIGITS_MAX);
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
        Assert::keyExists($normalizedProperty, 'digitsMin');

        return self::fromValues(intval($normalizedProperty['numberMin']), intval($normalizedProperty['digitsMin']));
    }

    public static function fromValues(int $numberMin, int $digitsMin): self
    {
        return new self($numberMin, $digitsMin);
    }

    /**
     * @return AutoNumberNormalized
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

    public function match(ProductProjection $productProjection): bool
    {
        return true;
    }
}
