<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class UpdateCatalogPayloadIsValid extends Compound
{
    /**
     * @param array<array-key, mixed> $options
     * @return array<array-key, Constraint>
     */
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'enabled' => new Assert\Required([
                        new Assert\Type('boolean'),
                    ]),
                    'product_selection_criteria' => [
                        new Assert\Type('array'),
                        new Assert\All(
                            new Assert\Collection([
                                'fields' => [
                                    'field' => [
                                        new Assert\Type('string'),
                                        new Assert\NotBlank(),
                                    ],
                                    'operator' => [
                                        new Assert\Type('string'),
                                        new Assert\NotBlank(),
                                    ],
                                    'value' => [
                                        new Assert\Optional(),
                                    ],
                                ],
                                'allowMissingFields' => false,
                                'allowExtraFields' => false,
                            ]),
                        ),
                        new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context): void {
                            if (!\is_array($array)) {
                                return;
                            }

                            if (\count(\array_filter(\array_keys($array), 'is_string')) > 0) {
                                $context->buildViolation('Invalid array structure.')
                                    ->addViolation();
                            }
                        }),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }
}
