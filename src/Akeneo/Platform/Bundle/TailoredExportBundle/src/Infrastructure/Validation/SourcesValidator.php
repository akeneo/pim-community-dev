<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\SimpleAssociationType\SimpleAssociationTypeSourceConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SourcesValidator extends ConstraintValidator
{
    private const MAX_SOURCE_COUNT = 4;
    private GetAttributes $getAttributes;
    private GetAssociationTypesInterface $getAssociationTypes;

    /** @var Constraint[] */
    private array $attributeConstraints;
    /** @var Constraint[] */
    private array $propertyConstraints;

    public function __construct(
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        array $attributeConstraints,
        array $propertyConstraints
    ) {
        $this->getAttributes = $getAttributes;
        $this->getAssociationTypes = $getAssociationTypes;
        $this->attributeConstraints = $attributeConstraints;
        $this->propertyConstraints = $propertyConstraints;
    }

    public function validate($sources, Constraint $constraint)
    {
        if (empty($sources)) {
            return;
        }

        $validator = $this->context->getValidator();
        $violations = $validator->validate($sources, [
            new Type(['type' => 'array']),
            new Count([
                'max' => self::MAX_SOURCE_COUNT,
                'maxMessage' => 'akeneo.tailored_export.validation.sources.max_source_count_reached'
            ])
        ]);

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->addViolation();
            }

            return;
        }

        foreach ($sources as $source) {
            $this->validateSource($validator, $source);
        }
    }

    private function validateSource(ValidatorInterface $validator, $source): void
    {
        $violations = $validator->validate(
            $source,
            new Collection([
                'fields' => [
                    'code' => [
                        new Type([
                            'type' => 'string',
                        ]),
                        new NotBlank(),
                        new AttributeShouldExist()
                    ],
                    'type' => new Choice(
                        [
                            'choices' => [AttributeSource::TYPE, PropertySource::TYPE, AssociationTypeSource::TYPE],
                        ]
                    ),

                ],
                'allowExtraFields' => true
            ]),
        );

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath(sprintf('[%s]%s', $source['uuid'], $violation->getPropertyPath()))
                    ->addViolation();
            }

            return;
        }

        if (PropertySource::TYPE === $source['type']) {
            $constraint = $this->propertyConstraints[$source['code']] ?? null;
        } elseif (AssociationTypeSource::TYPE === $source['type']) {
            $associationTypes = $this->getAssociationTypes->forCodes([$source['code']]);
            $associationType = $associationTypes[$source['code']] ?? null;
            if (null === $associationType) {
                $this->context->buildViolation(Sources::ASSOCIATION_TYPE_SHOULD_EXIST)
                    ->atPath(sprintf('[%s]', $source['uuid']))
                    ->setParameter('association_type_code', $source['code'])
                    ->addViolation();

                return;
            }

            if ($associationType->isQuantified()) {
                // @TODO Add two way validation (RAC-683)
                $constraint = null;
            } else {
                $constraint = new SimpleAssociationTypeSourceConstraint();
            }
        } elseif (AttributeSource::TYPE === $source['type']) {
            $attribute = $this->getAttributes->forCode($source['code']);

            if (null === $attribute) {
                //TODO handle attribute not found RAC-720
                return;
            }

            $constraint = $this->attributeConstraints[$attribute->type()] ?? null;
        } else {
            throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', $source['type']));
        }

        if (null === $constraint) {
            return;
        }

        $violations = $validator->validate($source, $constraint);

        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath(sprintf('[%s]%s', $source['uuid'], $violation->getPropertyPath()))
                ->addViolation();
        }
    }
}
