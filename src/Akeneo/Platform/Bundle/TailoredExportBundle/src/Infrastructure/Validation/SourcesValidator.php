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

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\PropertySource;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SourcesValidator extends ConstraintValidator
{
    private const MAX_SOURCE_COUNT = 4;
    private GetAttributes $getAttributes;

    /** @var Constraint[] */
    private array $attributeSelectionConstraints;

    /** @var Constraint[] */
    private array $propertySelectionConstraints;

    public function __construct(GetAttributes $getAttributes, array $attributeSelectionConstraints, array $propertySelectionConstraints)
    {
        $this->getAttributes = $getAttributes;
        $this->attributeSelectionConstraints = $attributeSelectionConstraints;
        $this->propertySelectionConstraints = $propertySelectionConstraints;
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
                        new ChannelShouldExist(),
                    ],
                    'locale' => [
                        new Type([
                            'type' => 'string',
                        ]),
                        new LocaleShouldBeActive()
                    ],
                    'operations' => [
                        new Type([
                            'type' => 'array',
                        ]),
                    ],
                    'type' => [
                        new Choice(
                            [
                                'strict' => true,
                                'choices' => [AttributeSource::TYPE, PropertySource::TYPE],
                            ]
                        )
                    ],
                    'selection' => new NotBlank(),
                ],
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

        $this->validateSelection($validator, $source);
    }

    private function validateSelection(ValidatorInterface $validator, array $source)
    {
        if (PropertySource::TYPE === $source['type']) {
            $constraint = $this->propertySelectionConstraints[$source['code']] ?? null;
        } else {
            $attribute = $this->getAttributes->forCode($source['code']);
            $constraint = $this->attributeSelectionConstraints[$attribute->type()] ?? null;
        }

        if (null === $constraint) {
            return;
        }

        $violations = $validator->validate($source['selection'], $constraint);
        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath(sprintf('[%s][selection]%s', $source['uuid'], $violation->getPropertyPath()))
                    ->addViolation();
            }
        }
    }
}
