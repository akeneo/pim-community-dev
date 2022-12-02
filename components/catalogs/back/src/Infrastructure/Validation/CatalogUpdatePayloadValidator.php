<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CatalogUpdatePayloadValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CatalogUpdatePayload) {
            throw new UnexpectedTypeException($constraint, CatalogUpdatePayload::class);
        }

        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->validate($value, $this->getConstraints($constraint));
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getConstraints(CatalogUpdatePayload $constraint): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'enabled' => new Assert\Required([
                        new Assert\Type('boolean'),
                    ]),
                    'product_selection_criteria' => new CatalogUpdateProductSelectionCriteriaPayload(),
                    'product_value_filters' => new CatalogUpdateProductValueFiltersPayload(),
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => true,
            ]),
            new CatalogUpdateProductMappingPayload(),
        ];
    }
}
