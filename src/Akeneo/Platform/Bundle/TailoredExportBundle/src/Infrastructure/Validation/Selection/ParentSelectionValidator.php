<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection;

use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExist;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ParentSelectionValidator extends ConstraintValidator
{
    public function validate($selection, Constraint $constraint)
    {
        $validator = $this->context->getValidator();
        $violations = $validator->validate($selection, [
            new Collection(
                [
                    'fields' => [
                        'type' => [
                            new NotBlank(),
                            new Choice(
                                [
                                    'strict' => true,
                                    'choices' => [
                                        SelectionTypes::CODE,
                                        SelectionTypes::LABEL,
                                    ],
                                ]
                            )
                        ],
                        'channel' => new Optional([new Type(['type' => 'string'])]),
                        'locale' => new Optional([new Type(['type' => 'string'])]),
                    ],
                ]
            ),
        ]);

        if (0 < $violations->count()) {
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath($violation->getPropertyPath())
                    ->addViolation();
            }

            return;
        }

        if (SelectionTypes::LABEL === $selection['type']) {
            $this->context->getValidator()
                ->inContext($this->context)
                ->atPath('[channel]')
                ->validate($selection['channel'], [
                    new NotBlank(),
                    new ChannelShouldExist(),
                ]);

            $this->context->getValidator()
                ->inContext($this->context)
                ->atPath('[locale]')
                ->validate($selection['locale'], [
                    new NotBlank(),
                    new LocaleShouldBeActive(),
                ]);
        }
    }
}
