<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

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
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ProductSelectionCriteriaValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductSelectionCriteria) {
            throw new UnexpectedTypeException($constraint, ProductSelectionCriteria::class);
        }


        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->validate($value, $this->getConstraints());
    }

    private function getConstraints(): array
    {
        return [
            new Assert\Type('array'),
            new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context): void {
                if (!\is_array($array)) {
                    return;
                }

                if (\count(\array_filter(\array_keys($array), 'is_string')) > 0) {
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
            default => null
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
