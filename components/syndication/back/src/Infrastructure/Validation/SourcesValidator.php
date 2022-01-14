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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\Syndication\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\AttributeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\Source\StaticSource;
use Akeneo\Platform\Syndication\Infrastructure\Validation\Source\QuantifiedAssociationType\QuantifiedAssociationTypeSourceConstraint;
use Akeneo\Platform\Syndication\Infrastructure\Validation\Source\SimpleAssociationType\SimpleAssociationTypeSourceConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
    /** @var Constraint[] */
    private array $staticConstraints;

    public function __construct(
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        array $attributeConstraints,
        array $propertyConstraints,
        array $staticConstraints
    ) {
        $this->getAttributes = $getAttributes;
        $this->getAssociationTypes = $getAssociationTypes;
        $this->attributeConstraints = $attributeConstraints;
        $this->propertyConstraints = $propertyConstraints;
        $this->staticConstraints = $staticConstraints;
    }

    public function validate($sources, Constraint $constraint): void
    {
        if (empty($sources)) {
            return;
        }

        $validator = $this->context->getValidator();
        $violations = $validator->validate($sources, [
            new Type(['type' => 'array']),
            new Count([
                'max' => self::MAX_SOURCE_COUNT,
                'maxMessage' => 'akeneo.syndication.validation.sources.max_source_count_reached'
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

    private function validateSource(ValidatorInterface $validator, array $source): void
    {
        switch ($source['type']) {
            case PropertySource::TYPE:
                $this->validatePropertySource($validator, $source);

                return;
            case AssociationTypeSource::TYPE:
                $this->validateAssociationTypeSource($validator, $source);

                return;
            case AttributeSource::TYPE:
                $this->validateAttributeSource($validator, $source);

                return;
            case StaticSource::TYPE:
                $this->validateStaticSource($validator, $source);

                return;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', $source['type']));
        }
    }

    private function validatePropertySource(ValidatorInterface $validator, array $source): void
    {
        $constraint = $this->propertyConstraints[$source['code']] ?? null;

        if (!$constraint instanceof Constraint) {
            return;
        }

        $violations = $validator->validate($source, $constraint);
        $this->buildViolations($violations, $source);
    }

    private function validateStaticSource(ValidatorInterface $validator, array $source): void
    {
        $constraint = $this->staticConstraints[$source['code']] ?? null;

        if (!$constraint instanceof Constraint) {
            return;
        }

        $violations = $validator->validate($source, $constraint);
        $this->buildViolations($violations, $source);
    }

    private function validateAssociationTypeSource(ValidatorInterface $validator, array $source): void
    {
        $associationTypes = $this->getAssociationTypes->forCodes([$source['code']]);
        $associationType = $associationTypes[$source['code']] ?? null;

        if (!$associationType instanceof AssociationType) {
            $this->context->buildViolation(Sources::ASSOCIATION_TYPE_SHOULD_EXIST)
                ->atPath(sprintf('[%s]', $source['uuid']))
                ->setParameter('association_type_code', $source['code'])
                ->addViolation();

            return;
        }

        $constraint = $associationType->isQuantified() ?
            new QuantifiedAssociationTypeSourceConstraint() :
            new SimpleAssociationTypeSourceConstraint();

        $violations = $validator->validate($source, $constraint);
        $this->buildViolations($violations, $source);
    }

    private function validateAttributeSource(ValidatorInterface $validator, array $source): void
    {
        $attribute = $this->getAttributes->forCode($source['code']);

        if (!$attribute instanceof Attribute) {
            $this->context->buildViolation(
                Sources::ATTRIBUTE_SHOULD_EXIST,
                [
                    '{{ attribute_code }}' => $source['code'],
                ]
            )
                ->atPath(sprintf('[%s]', $source['uuid']))
                ->addViolation();

            return;
        }

        $constraint = $this->attributeConstraints[$attribute->type()] ?? null;

        if (!$constraint instanceof Constraint) {
            return;
        }

        $violations = $validator->validate($source, [new IsValidAttribute(), $constraint]);
        $this->buildViolations($violations, $source);
    }

    private function buildViolations(ConstraintViolationListInterface $violations, array $source): void
    {
        foreach ($violations as $violation) {
            $builder = $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath(sprintf('[%s]%s', $source['uuid'], $violation->getPropertyPath()));
            if ($violation->getPlural()) {
                $builder->setPlural((int)$violation->getPlural());
            }
            $builder->addViolation();
        }
    }
}
