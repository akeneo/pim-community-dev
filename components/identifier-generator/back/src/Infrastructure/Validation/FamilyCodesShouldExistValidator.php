<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyCodesShouldExistValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindFamilyCodes $findFamilyCodes,
    ) {
    }

    public function validate($familyCodes, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FamilyCodesShouldExist::class);

        if (!\is_array($familyCodes)) {
            return;
        }

        if (\count($familyCodes) === 0) {
            return;
        }

        foreach ($familyCodes as $familyCode) {
            if (!\is_string($familyCode)) {
                return;
            }
        }

        $existingCodes = $this->findFamilyCodes->fromQuery(new FamilyQuery(includeCodes: $familyCodes));
        $nonExistingCodes = \array_diff($familyCodes, $existingCodes);
        if (\count($nonExistingCodes) > 0) {
            $this->context
                ->buildViolation($constraint->familiesDoNotExist, [
                    '{{ familyCodes }}' =>  \implode(', ', \array_map(fn (string $value): string => (string) \json_encode($value), $nonExistingCodes)),
                ])
                ->addViolation();
        }
    }
}
