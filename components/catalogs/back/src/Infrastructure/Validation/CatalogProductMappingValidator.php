<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeBooleanSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeDateSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeIdentifierSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeNumberSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeSimpleSelectSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeTextareaSource;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeTextSource;
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
final class CatalogProductMappingValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CatalogProductMapping) {
            throw new UnexpectedTypeException($constraint, CatalogProductMapping::class);
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
            new Assert\Sequentially([
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
                new Assert\All([
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
            ]),
        ];
    }

    private function getMappingSourceConstraint(string $source): Constraint|null
    {
        $constraint = match ($source) {
            'uuid' => new UuidSource(),
            default => null,
        };

        if (null !== $constraint) {
            return $constraint;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($source);

        return match ($attribute['type'] ?? null) {
            'pim_catalog_boolean' => new AttributeBooleanSource(),
            'pim_catalog_date' => new AttributeDateSource(),
            'pim_catalog_identifier' => new AttributeIdentifierSource(),
            'pim_catalog_number' => new AttributeNumberSource(),
            'pim_catalog_simpleselect' => new AttributeSimpleSelectSource(),
            'pim_catalog_text' => new AttributeTextSource(),
            'pim_catalog_textarea' => new AttributeTextareaSource(),
            default => null,
        };
    }
}
