<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeBooleanCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeDateCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeIdentifierCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeMeasurementCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeMultiSelectCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeNumberCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeSimpleSelectCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeTextareaCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeTextCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\CategoriesCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\CompletenessCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\EnabledCriterion;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\FamilyCriterion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CatalogProductSelectionCriteriaValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private int $maxCriteriaPerCatalog,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CatalogProductSelectionCriteria) {
            throw new UnexpectedTypeException($constraint, CatalogProductSelectionCriteria::class);
        }

        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->validate($value, $this->getConstraints());
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getConstraints(): array
    {
        $maxCriteriaPerCatalog = $this->maxCriteriaPerCatalog;
        return [
            new Assert\Type('array'),
            new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context) use ($maxCriteriaPerCatalog): void {
                if (!\is_array($array)) {
                    return;
                }

                if (\count($array) > $maxCriteriaPerCatalog) {
                    $context->buildViolation('Too many criteria.')
                        ->addViolation();
                }

                if (!\array_is_list($array)) {
                    $context->buildViolation('Invalid array structure.')
                        ->addViolation();
                }
            }),
            new Assert\All([
                new Assert\Callback(function (mixed $criterion, ExecutionContextInterface $context): void {
                    if (!\is_array($criterion)) {
                        return;
                    }

                    if (!isset($criterion['field']) || !\is_string($criterion['field'])) {
                        $context->buildViolation('Missing field value')
                            ->atPath('[field]')
                            ->addViolation();

                        return;
                    }

                    $constraint = $this->getCriterionConstraint($criterion['field']);

                    if (null === $constraint) {
                        $context->buildViolation('Invalid field value')
                            ->atPath('[field]')
                            ->addViolation();

                        return;
                    }

                    $context
                        ->getValidator()
                        ->inContext($this->context)
                        ->validate($criterion, $constraint);
                }),
            ]),
        ];
    }

    private function getCriterionConstraint(string $field): Constraint|null
    {
        $constraint = match ($field) {
            'categories' => new CategoriesCriterion(),
            'completeness' => new CompletenessCriterion(),
            'enabled' => new EnabledCriterion(),
            'family' => new FamilyCriterion(),
            default => null,
        };

        if (null !== $constraint) {
            return $constraint;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($field);

        return match ($attribute['type'] ?? null) {
            'pim_catalog_identifier' => new AttributeIdentifierCriterion(),
            'pim_catalog_text' => new AttributeTextCriterion(),
            'pim_catalog_textarea' => new AttributeTextareaCriterion(),
            'pim_catalog_simpleselect' => new AttributeSimpleSelectCriterion(),
            'pim_catalog_multiselect' => new AttributeMultiSelectCriterion(),
            'pim_catalog_number' => new AttributeNumberCriterion(),
            'pim_catalog_metric' => new AttributeMeasurementCriterion(),
            'pim_catalog_boolean' => new AttributeBooleanCriterion(),
            'pim_catalog_date' => new AttributeDateCriterion(),
            default => null,
        };
    }
}
