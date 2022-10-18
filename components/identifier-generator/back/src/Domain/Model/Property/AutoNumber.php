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
    public function __construct(
        private int $numberMin,
        private int $digitsMin,
    ) {
    }

    public static function type(): string
    {
        return 'auto_number';
    }

    public static function fromNormalized(array $normalizedProperty): PropertyInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::same($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'numberMin');
        Assert::greaterThanEq($normalizedProperty['numberMin'], 0);
        Assert::keyExists($normalizedProperty, 'digitsMin');
        Assert::greaterThanEq($normalizedProperty['digitsMin'], 1);

        return self::fromValues($normalizedProperty['numberMin'], $normalizedProperty['digitsMin']);
    }

    public static function fromValues(int $numberMin, int $digitsMin): self
    {
        Assert::greaterThanEq($numberMin, 0);
        Assert::greaterThanEq($digitsMin, 0);

        return new self($numberMin, $digitsMin);
    }

    public function normalize()
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
