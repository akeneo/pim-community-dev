<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyPropertyShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate($property, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FamilyPropertyShouldBeValid::class);
        if (!\is_array($property)) {
            return;
        }
        if (!\array_key_exists('type', $property)) {
            return;
        }
        if ($property['type'] !== FamilyProperty::type()) {
            return;
        }

        if (!\array_key_exists('process', $property)) {
            $this->context
                ->buildViolation($constraint->fieldsRequired, [
                    '{{ field }}' => 'process',
                ])
                ->addViolation();

            return;
        }

        if (!\array_key_exists('type', $property['process'])) {
            return;
        }

        switch ($property['process']['type']) {
            case Process::PROCESS_TYPE_NO:
                $this->validateProcessTypeNo($property['process']);

                break;
            case Process::PROCESS_TYPE_TRUNCATE:
                $this->validateProcessTypeTruncate($property['process'], $constraint);

                break;
        }
    }

    /**
     * @param array<string, mixed> $property
     */
    private function validateProcessTypeNo(array $property): void
    {
        $this->validator->inContext($this->context)->validate($property, new Collection([
            'fields' => [
                'type' => null,
            ],
        ]));
    }

    /**
     * @param array<string, mixed> $property
     */
    private function validateProcessTypeTruncate(array $property, FamilyPropertyShouldBeValid $constraint): void
    {
        $this->validator->inContext($this->context)->validate($property, new Collection([
            'fields' => [
                'type' => null,
                'operator' => new Choice(
                    choices: [Process::PROCESS_OPERATOR_EQ, Process::PROCESS_OPERATOR_LTE],
                    message: $constraint->processUnknownOperator
                ),
                'value' => [
                    new Type([
                        'type' => 'digit',
                    ]),
                    new Range([
                        'min' => 1,
                        'max' => 5,
                    ]),
                ],
            ],
        ]));
    }
}
