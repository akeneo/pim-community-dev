<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\FindUnit;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\Unit;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TargetValidator extends ConstraintValidator
{
    private const ATTRIBUTE_TYPE_NUMBER = 'pim_catalog_number';
    private const ATTRIBUTE_TYPE_METRIC = 'pim_catalog_metric';

    public function __construct(
        private GetAttributes $getAttributes,
        private FindUnit $findUnit,
        private array $supportedProperties,
    ) {
    }

    public function validate($target, Constraint $constraint): void
    {
        if (!$constraint instanceof Target) {
            throw new UnexpectedTypeException($constraint, Target::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($target, new Collection([
            'fields' => [
                'code' => [
                    new Type('string'),
                    new NotBlank(),
                ],
                'type' => [
                    new Choice([
                        'choices' => [
                            'attribute',
                            'property',
                        ]
                    ]),
                ],
                'action_if_not_empty' => [
                    new Choice([
                        'choices' => [
                            TargetInterface::ACTION_SET,
                            TargetInterface::ACTION_ADD,
                        ]
                    ]),
                ],
                'action_if_empty' => [
                    new Choice([
                        'choices' => [
                            TargetInterface::IF_EMPTY_CLEAR,
                            TargetInterface::IF_EMPTY_SKIP,
                        ]
                    ]),
                ],
            ],
            'allowExtraFields' => true,
        ]));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        if ('attribute' === $target['type']) {
            $this->validateAttributeTarget($validator, $target);
        } else {
            $this->validatePropertyTarget($target);
        }
    }

    private function validateAttributeTarget(ValidatorInterface $validator, array $attributeTarget): void
    {
        $attribute = $this->getAttributes->forCode($attributeTarget['code']);
        if (!$attribute instanceof Attribute) {
            $this->context->buildViolation(
                Target::ATTRIBUTE_SHOULD_EXIST,
                [
                    '{{ attribute_code }}' => $attributeTarget['code'],
                ]
            )
                ->atPath('[code]')
                ->addViolation();

            return;
        }

        $validator->inContext($this->context)->validate($attributeTarget, new Collection([
            'fields' => [
                'channel' => [
                    new Type('string'),
                    new ChannelShouldExist(),
                ],
                'locale' => [
                    new Type('string'),
                    new LocaleShouldBeActive(),
                ],
            ],
            'allowExtraFields' => true,
        ]));

        $this->validateSourceParameter($validator, $attributeTarget, $attribute);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $validator->inContext($this->context)->validate($attributeTarget, new IsValidAttribute());
    }

    private function validatePropertyTarget(array $propertyTarget): void
    {
        if (!in_array($propertyTarget['code'], $this->supportedProperties)) {
            $this->context->buildViolation(
                Target::PROPERTY_SHOULD_EXIST,
                [
                    '{{ property_code }}' => $propertyTarget['code'],
                ]
            )
                ->atPath('[code]')
                ->addViolation();
        }
    }

    private function validateSourceParameter(ValidatorInterface $validator, array $attributeTarget, Attribute $attribute): void
    {
        $typesRequiringSourceParameter = [
            self::ATTRIBUTE_TYPE_NUMBER,
            self::ATTRIBUTE_TYPE_METRIC,
        ];

        if (!in_array($attribute->type(), $typesRequiringSourceParameter)) {
            return;
        }

        $validator->inContext($this->context)->validate($attributeTarget, new Collection([
            'fields' => [
                'source_parameter' => [
                    new Type('array'),
                    new NotBlank(),
                ],
            ],
            'allowExtraFields' => true,
        ]));

        if ($attribute->type() === self::ATTRIBUTE_TYPE_METRIC) {
            $unitCode = $attributeTarget['source_parameter']['unit'];
            $unit = $this->findUnit->byMeasurementFamilyCodeAndUnitCode($attribute->metricFamily(), $unitCode);

            if (!$unit instanceof Unit) {
                $this->context->buildViolation(
                    Target::MEASUREMENT_UNIT_SHOULD_EXIST,
                    [
                        '{{ unit_code }}' => $unitCode,
                        '{{ measurement_family }}' => $attribute->metricFamily(),
                    ]
                )
                ->atPath('[source_parameter]')
                ->addViolation();

                return;
            }
        }
    }
}
