<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SelectOptionShouldExistValidator extends ConstraintValidator
{
    public function __construct(
        private readonly GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues,
    ) {
    }

    public function validate($selectCondition, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, SelectOptionShouldExist::class);

        if (!\is_array($selectCondition)) {
            return;
        }

        if (!\array_key_exists('attributeCode', $selectCondition)) {
            return;
        }

        if (!\array_key_exists('value', $selectCondition)) {
            return;
        }

        if (!\is_array($selectCondition['value'])) {
            return;
        }

        if (\count($selectCondition['value']) === 0) {
            return;
        }

        foreach ($selectCondition['value'] as $value) {
            if (!\is_string($value)) {
                return;
            }
        }

        $parameters = \array_map(fn (string $value): string => \sprintf('%s.%s', $selectCondition['attributeCode'], $value), $selectCondition['value']);

        $existingOptionsWithAttributeCode = \array_keys($this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes($parameters));
        $existingOptions = \array_map(fn (string $optionWithAttribute): string => \explode('.', $optionWithAttribute)[1], $existingOptionsWithAttributeCode);

        $nonExistingCodes = \array_diff($selectCondition['value'], $existingOptions);
        if (\count($nonExistingCodes) > 0) {
            $this->context
                ->buildViolation($constraint->optionsDoNotExist, [
                    '{{ attributeCode }}' => $selectCondition['attributeCode'],
                    '{{ optionCodes }}' =>  \implode(', ', \array_map(fn (string $value): string => (string) \json_encode($value), $nonExistingCodes)),
                ])
                ->atPath('[value]')
                ->addViolation();
        }
    }
}
