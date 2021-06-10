<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class AreTableAttributeValidationsValidValidator extends ConstraintValidator
{
    private const VALIDATION_KEYS = ['max_length'];

    public function validate($value, Constraint $constraint): void
    {
        Assert::implementsInterface($value, AttributeInterface::class);
        Assert::isInstanceOf($constraint, AreTableAttributeValidationsValid::class);
        if (AttributeTypes::TABLE !== $value->getType() || null === $value->getRawTableConfiguration()) {
            return;
        }

        foreach ($value->getRawTableConfiguration() as $index => $rawColumnDefinition) {
            $validations = $rawColumnDefinition['validations'] ?? [];
            if (!\is_array($validations)) {
                // already validated elsewhere
                continue;
            }
            foreach ($validations as $validationKey => $validationValue) {
                if (!\in_array($validationKey, self::VALIDATION_KEYS, true)) {
                    $this->context->buildViolation(
                        'TODO unknown validation key {{ expected }} => {{ given }}',
                        [
                            '{{ expected }}' => \implode(', ', self::VALIDATION_KEYS),
                            '{{ given }}' => $validationKey,
                        ]
                    )->atPath(\sprintf('table_configuration[%d].validations', $index))
                                  ->addViolation();
                    continue;
                }
                if (!\is_int($validationValue)) {
                    $this->context->buildViolation(
                        'TODO invalid validation value type {{ expected }} => {{ given }}',
                        [
                            '{{ expected }}' => 'integer',
                            '{{ given }}' => \is_object($validationValue) ? get_class($validationValue) : \gettype(
                                $validationValue
                            ),
                        ]
                    )->atPath(\sprintf('table_configuration[%d].validations.%s', $index, $validationKey))
                                  ->addViolation();
                    continue;
                }
                if ($validationValue <= 0) {
                    $this->context->buildViolation(
                        'TODO invalid max_length should be a positive integer {{ given }}',
                        [
                            '{{ given }}' => $validationValue,
                        ]
                    )->atPath(\sprintf('table_configuration[%d].validations.%s', $index, $validationKey))
                                  ->addViolation();
                }
            }
        }
    }
}
