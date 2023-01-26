<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProcessOperator 'EQUALS'|'LOWER_OR_EQUAL_THAN'
 * @phpstan-type ProcessNormalized array{type: 'no'}|array{type: 'truncate', operator: string, value: int}
 */
final class Process
{
    public const PROCESS_TYPE_NO = 'no';
    public const PROCESS_TYPE_TRUNCATE = 'truncate';

    public const PROCESS_OPERATOR_EQ = 'EQUALS';
    public const PROCESS_OPERATOR_LTE = 'LOWER_OR_EQUAL_THAN';

    private function __construct(
        private string $type,
        private ?string $operator,
        private ?int $value
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    public function operator(): ?string
    {
        return $this->operator;
    }

    public function value(): ?int
    {
        return $this->value;
    }

    /**
     * @return ProcessNormalized
     */
    public function normalize(): array
    {
        if ($this->type === self::PROCESS_TYPE_NO) {
            return [
                'type' => self::PROCESS_TYPE_NO,
            ];
        }

        Assert::stringNotEmpty($this->operator);
        Assert::integer($this->value);

        return [
            'type' => self::PROCESS_TYPE_TRUNCATE,
            'operator' => $this->operator,
            'value' => $this->value,
        ];
    }

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): Process
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::oneOf($normalizedProperty['type'], [self::PROCESS_TYPE_NO, self::PROCESS_TYPE_TRUNCATE]);
        $operator = null;
        $value = null;
        if ('truncate' === $normalizedProperty['type']) {
            Assert::keyExists($normalizedProperty, 'operator');
            Assert::notEmpty($normalizedProperty['operator']);
            Assert::oneOf($normalizedProperty['operator'], [self::PROCESS_OPERATOR_EQ, self::PROCESS_OPERATOR_LTE]);
            $operator = $normalizedProperty['operator'];

            Assert::keyExists($normalizedProperty, 'value');
            Assert::integer($normalizedProperty['value']);
            Assert::greaterThanEq($normalizedProperty['value'], 1);
            Assert::lessThanEq($normalizedProperty['value'], 5);
            $value = $normalizedProperty['value'];
        }

        return new self($normalizedProperty['type'], $operator, $value);
    }
}
