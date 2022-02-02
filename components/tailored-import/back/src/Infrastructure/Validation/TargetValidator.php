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
    public function __construct(
        private GetAttributes $getAttributes,
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
                'action' => [
                    new Choice([
                        'choices' => [
                            'set',
                            'add',
                        ]
                    ]),
                ],
                'ifEmpty' => [
                    new Choice([
                        'choices' => [
                            'clear',
                            'skip',
                        ]
                    ]),
                ],
                'onError' => [
                    new Choice([
                        'choices' => [
                            'skipLine',
                            'skipValue',
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
}
