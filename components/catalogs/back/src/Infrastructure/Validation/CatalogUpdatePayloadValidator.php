<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeTextCriterion\AttributeTextCriterionStructure;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeTextCriterion\AttributeTextCriterionValues;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CompletenessCriterion\CompletenessCriterionStructure;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CompletenessCriterion\CompletenessCriterionValues;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\EnabledCriterion\EnabledCriterionStructure;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\FamilyCriterion\FamilyCriterionStructure;
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
class CatalogUpdatePayloadValidator extends ConstraintValidator
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
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
        return [
            new Assert\Collection([
                'fields' => [
                    'enabled' => new Assert\Required([
                        new Assert\Type('boolean'),
                    ]),
                    'product_selection_criteria' => [
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

                                $constraints = $criterion['field'] ? $this->getCriterionConstraint(
                                    $criterion['field']
                                ) : null;

                                if (null === $constraints) {
                                    $context->buildViolation('Invalid field value')
                                        ->atPath('[field]')
                                        ->addViolation();

                                    return;
                                }

                                $context
                                    ->getValidator()
                                    ->inContext($this->context)
                                    ->validate($criterion, $constraints);
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
            'completeness' => new Assert\Sequentially([
                new CompletenessCriterionStructure(),
                new CompletenessCriterionValues(),
            ]),
            'enabled' => new EnabledCriterionStructure(),
            'family' => new FamilyCriterionStructure(),
            default => null
        };

        if (null !== $constraint) {
            return $constraint;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($field);

        return match ($attribute['type'] ?? null) {
            'pim_catalog_text' => new Assert\Sequentially([
                new AttributeTextCriterionStructure(),
                new AttributeTextCriterionValues(),
            ]),
            default => null,
        };
    }
}
