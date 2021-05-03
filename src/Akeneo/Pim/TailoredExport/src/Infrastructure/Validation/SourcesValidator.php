<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SourcesValidator extends ConstraintValidator
{
    public function validate($sources, Constraint $constraint)
    {
        if (empty($sources)) {
            return;
        }

        $validator = Validation::createValidator();

        foreach ($sources as $source) {
            $this->validateColumn($validator, $source);
        }
    }

    private function validateColumn(ValidatorInterface $validator, $source): void
    {
        $violations = $validator->validate(
            $source,
            new Collection([
                'fields' => [
                    'uuid' => [
                        new NotBlank(),
                        new Uuid()
                    ],
                    'code' => [
                        new Type([
                            'type' => 'string',
                        ]),
                        new NotBlank(),
                    ],
                    'channel' => [
                        new Type([
                            'type' => 'string',
                        ]),
                    ],
                    'locale' => [
                        new Type([
                            'type' => 'string',
                        ]),
                    ],
                    'operations' => [
                        new Type([
                            'type' => 'array',
                        ]),
                    ],
                    'selection' => new Collection(
                        ['fields' => [
                            'type' => new Type(['type' => 'string'])
                        ]]
                    ),
                ],
            ]),
        );

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath(sprintf('[%s]', $source['uuid']))
                    ->addViolation();
            }
        }
    }
}
