<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyPropertyShouldBeValidValidator extends ConstraintValidator
{
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

        $processKeys = [
            Process::PROCESS_TYPE_NO,
            Process::PROCESS_TYPE_TRUNCATE,
            Process::PROCESS_TYPE_NOMENCLATURE
        ];
        if (!in_array($property['process'], $processKeys)) {
            $this->context
                ->buildViolation($constraint->processFieldsRequired, [
                    '{{field}}' => implode(',', $processKeys),
                ])
                ->addViolation();
        }
    }
}
