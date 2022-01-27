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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TargetValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'code' => [
                    new Type('string'),
                    new NotBlank(),
                ],
                'type' => [
                    new Choice([
                        'choices' => [
                            'attribute',
                            'property'
                        ]
                    ]),
                ],
                'action' => [
                    new Choice([
                        'choices' => [
                            'set',
                            'add'
                        ]
                    ]),
                ],
                'ifEmpty' => [
                    new Choice([
                        'choices' => [
                            'clear',
                            'skip'
                        ]
                    ]),
                ],
                'onError' => [
                    new Choice([
                        'choices' => [
                            'skipLine',
                            'skipValue'
                        ]
                    ]),
                ],
            ],
            'allowExtraFields' => true,
        ]));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        if ('attribute' === $value['type']) {
            $this->validateAttributeTarget($validator, $value);
        }
    }

    private function validateAttributeTarget(ValidatorInterface $validator, array $attributeTarget): void
    {
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
    }
}
