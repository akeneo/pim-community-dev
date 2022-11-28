<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeTextSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\SystemSource\UuidSource;
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
use Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters\FilterContainsActivatedCurrency;
use Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters\FilterContainsActivatedLocale;
use Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters\FilterContainsValidChannel;
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
final class CatalogUpdatePayloadValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private int $maxCriteriaPerCatalog,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CatalogUpdatePayload) {
            throw new UnexpectedTypeException($constraint, CatalogUpdatePayload::class);
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
            new Assert\Collection([
                'fields' => [
                    'enabled' => new Assert\Required([
                        new Assert\Type('boolean'),
                    ]),
                    'product_selection_criteria' => [
                        new Assert\Type('array'),
                        new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context) use ($maxCriteriaPerCatalog): void {
                            if (!\is_array($array)) {
                                return;
                            }

                            if (count($array) > $maxCriteriaPerCatalog) {
                                $context->buildViolation('Too many criteria.')
                                    ->addViolation();
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
                    ],
                    'product_value_filters' => [
                        new Assert\Collection([
                            'channels' => new Assert\Optional([
                                new Assert\Type('array'),
                                new Assert\All([
                                    'constraints' => [
                                        new Assert\Type('string'),
                                        new FilterContainsValidChannel(),
                                    ],
                                ]),
                            ]),
                            'locales' => new Assert\Optional([
                                new Assert\Type('array'),
                                new Assert\All([
                                    'constraints' => [
                                        new Assert\Type('string'),
                                        new FilterContainsActivatedLocale(),
                                    ],
                                ]),
                            ]),
                            'currencies' => new Assert\Optional([
                                new Assert\Type('array'),
                                new Assert\All([
                                    'constraints' => [
                                        new Assert\Type('string'),
                                        new FilterContainsActivatedCurrency(),
                                    ],
                                ]),
                            ]),
                        ]),
                    ],
                    'product_mapping' => [
                        new Assert\Type('array'),
                        new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context): void {
                            if (!\is_array($array)) {
                                return;
                            }

                            if (\count(\array_filter(\array_keys($array), 'is_string')) !== \count($array)) {
                                $context->buildViolation('Invalid array structure.')
                                    ->addViolation();
                            }
                        }),
                        new Assert\All([
                            new Assert\Callback(function (mixed $sourceAssociation, ExecutionContextInterface $context): void {
                                if (!\is_array($sourceAssociation) || null === $sourceAssociation['source']) {
                                    return;
                                }

                                if (!\is_string($sourceAssociation['source'])) {
                                    $context->buildViolation('Unknown source value')
                                        ->atPath('[source]')
                                        ->addViolation();

                                    return;
                                }

                                $constraint = $this->getMappingSourceConstraint($sourceAssociation['source']);

                                if (null === $constraint) {
                                    $context->buildViolation('Invalid source value')
                                        ->atPath('[source]')
                                        ->addViolation();

                                    return;
                                }

                                $context
                                    ->getValidator()
                                    ->inContext($this->context)
                                    ->validate($sourceAssociation, $constraint);
                            }),
                        ]),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
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

    private function getMappingSourceConstraint(string $source): Constraint|null
    {
        $constraint = match ($source) {
            'uuid' => new UuidSource(),
            default => null
        };

        if (null !== $constraint) {
            return $constraint;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($source);

        return match ($attribute['type'] ?? null) {
            'pim_catalog_text' => new AttributeTextSource(),
            default => null,
        };
    }
}
