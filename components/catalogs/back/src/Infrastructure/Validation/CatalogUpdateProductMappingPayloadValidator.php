<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeTextSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\ProductMappingRespectsSchema;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\SystemSource\UuidSource;
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
final class CatalogUpdateProductMappingPayloadValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CatalogUpdateProductMappingPayload) {
            throw new UnexpectedTypeException($constraint, CatalogUpdateProductMappingPayload::class);
        }

        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->validate($value, $this->getConstraints($constraint));
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getConstraints(CatalogUpdateProductMappingPayload $constraint): array
    {
        return [
            new Assert\Sequentially([
                new Assert\Collection([
                    'fields' => [
                        'product_mapping' => [
                            new Assert\Type('array'),
                            new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context): void {
                                if (!\is_array($array) || empty($array)) {
                                    return;
                                }

                                if (\array_is_list($array)) {
                                    $context->buildViolation('Invalid array structure.')
                                        ->addViolation();
                                }
                            }),
                        ],
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => true,
                ]),
                new ProductMappingRespectsSchema(),
                new Assert\Collection([
                    'fields' => [
                        'product_mapping' => new Assert\All([
                            new Assert\Callback(function (mixed $sourceAssociation, ExecutionContextInterface $context): void {
                                if (!\is_array($sourceAssociation) || null === $sourceAssociation['source']) {
                                    return;
                                }

                                if (!\is_string($sourceAssociation['source'])) {
                                    $context->buildViolation('akeneo_catalogs.validation.product_mapping.source.unknown')
                                        ->atPath('[source]')
                                        ->addViolation();

                                    return;
                                }

                                $constraint = $this->getMappingSourceConstraint($sourceAssociation['source']);

                                if (null === $constraint) {
                                    $context->buildViolation('akeneo_catalogs.validation.product_mapping.source.invalid')
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
                    'allowMissingFields' => false,
                    'allowExtraFields' => true,
                ]),
            ])
        ];
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
