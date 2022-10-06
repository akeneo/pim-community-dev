<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\Family\GetFamiliesByCodeQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FamilyCriterionContainsValidFamiliesValidator extends ConstraintValidator
{
    public function __construct(private GetFamiliesByCodeQueryInterface $getFamiliesByCodeQuery)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FamilyCriterionContainsValidFamilies) {
            throw new UnexpectedTypeException($constraint, FamilyCriterionContainsValidFamilies::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var array<array-key, string> $familyCodes */
        $familyCodes = $value['value'];

        if ([] === $familyCodes) {
            return;
        }

        $paginatedFamilyCodes = \array_chunk($familyCodes, 20);
        foreach ($paginatedFamilyCodes as $familyCodePage) {
            if ($this->containsUnknownFamily($familyCodePage)) {
                $this->context
                    ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.family.unknown')
                    ->atPath('[value]')
                    ->addViolation();

                break;
            }
        }
    }

    /**
     * @param array<string> $codes
     */
    private function containsUnknownFamily(array $codes): bool
    {
        $codesCount = \count($codes);
        $existingCodes = $this->getFamiliesByCodeQuery->execute($codes, 1, $codesCount);
        return \count($existingCodes) !== $codesCount;
    }
}
