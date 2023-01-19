<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProcessOperator 'EQUALS'|'LOWER_OR_EQUAL_THAN'
 * @phpstan-type ProcessNormalized array{type: 'no'|'truncate'|'nomenclature', operator?: string, value?: int}
 */
final class Process
{
    public const PROCESS_TYPE_NO = 'no';
    public const PROCESS_TYPE_TRUNCATE = 'truncate';
    public const PROCESS_TYPE_NOMENCLATURE = 'nomenclature';

    public const PROCESS_OPERATOR_EQ = 'EQUALS';
    public const PROCESS_OPERATOR_LTE = 'LOWER_OR_EQUAL_THAN';

    public function __construct(
        private string $type,
        private ?string $operator,
        private ?int $value
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return ProcessNormalized
     */
    public function normalize(): array
    {
        return \array_filter([
            'type' => $this->type,
            'operator' => $this->operator,
            'value' => $this->value,
        ]);
    }

    /**
     * @inerhitdoc
     */
    public static function fromNormalized(array $normalizedProperty): Process
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::oneOf($normalizedProperty['type'], [self::PROCESS_TYPE_NO, self::PROCESS_TYPE_TRUNCATE, self::PROCESS_TYPE_NOMENCLATURE]);
        if ('truncate' === $normalizedProperty['type']) {
            Assert::keyExists($normalizedProperty, 'operator');
            Assert::notEmpty($normalizedProperty['operator']);
            Assert::oneOf($normalizedProperty['operator'], [self::PROCESS_OPERATOR_EQ, self::PROCESS_OPERATOR_LTE]);

            Assert::keyExists($normalizedProperty, 'value');
            Assert::numeric($normalizedProperty['value']);
            $normalizedProperty['value'] = intval($normalizedProperty['value']);
        }
        $operator = array_key_exists('operator', $normalizedProperty) ? $normalizedProperty['operator'] : null;
        $value = array_key_exists('value', $normalizedProperty) ? $normalizedProperty['value'] : null;

        return new self($normalizedProperty['type'], $operator, $value);
    }
}
