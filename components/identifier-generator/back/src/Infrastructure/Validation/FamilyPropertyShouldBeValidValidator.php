<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
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
                    '{{field}}' => 'process',
                ])
                ->addViolation();
            return;
        }

        if (!\array_key_exists('type', $property['process'])) {
            return;
        }

        if (Process::PROCESS_TYPE_NO === $property['process']['type']) {
            $this->validateProcessTypeNo($property, $constraint);
        }
    }

    public function validateProcessTypeNo(array $property, FamilyPropertyShouldBeValid $constraint): void
    {
        $this->validator->inContext($this->context)->validate($property, new Collection([
            'fields' => [
                'type' => null,
            ],
            'extraFieldsMessage' => $constraint->processTypeNoOtherProperties
        ]));
    }
}
