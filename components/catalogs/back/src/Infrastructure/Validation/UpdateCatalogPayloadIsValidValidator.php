<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\GetChannelsQueryInterface;
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
class UpdateCatalogPayloadIsValidValidator extends ConstraintValidator
{
    public function __construct(private GetChannelsQueryInterface $getChannelsQuery)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UpdateCatalogPayloadIsValid) {
            throw new UnexpectedTypeException($constraint, UpdateCatalogPayloadIsValid::class);
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
            new Assert\Collection([
                'fields' => [
                    'enabled' => new Assert\Required([
                        new Assert\Type('boolean'),
                    ]),
                    'product_selection_criteria' => [
                        new Assert\Type('array'),
                        new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context): void {
                            if (!\is_array($array)) {
                                return;
                            }

                            if (\count(\array_filter(\array_keys($array), 'is_string')) > 0) {
                                $context->buildViolation('Invalid array structure.')
                                    ->addViolation();
                            }
                        }),
                        new Assert\All([
                            new Assert\Callback(function (mixed $criterion, ExecutionContextInterface $context): void {
                                if (!\is_array($criterion)) {
                                    return;
                                }

                                $constraints = match ($criterion['field'] ?? null) {
                                    'enabled' => $this->getEnabledConstraints(),
                                    'family' => $this->getFamilyConstraints(),
                                    'completeness' => $this->getCompletenessConstraints(),
                                    default => []
                                };

                                if ($constraints === []) {
                                    $context->buildViolation('Invalid field value')
                                        ->atPath('[field]')
                                        ->addViolation();

                                    return;
                                }

                                $context
                                    ->getValidator()
                                    ->inContext($this->context)
                                    ->validate($criterion, $constraints);
                            }),
                        ]),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getEnabledConstraints(): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'field' => [
                        new Assert\IdenticalTo('enabled'),
                    ],
                    'operator' => [
                        new Assert\Type('string'),
                        new Assert\Choice(['=', '!=']),
                    ],
                    'value' => [
                        new Assert\Type('boolean'),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getFamilyConstraints(): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'field' => [
                        new Assert\IdenticalTo('family'),
                    ],
                    'operator' => [
                        new Assert\Type('string'),
                        new Assert\Choice(['EMPTY', 'NOT EMPTY', 'IN', 'NOT IN']),
                    ],
                    'value' => [
                        new Assert\Type('array'),
                        new Assert\All(new Assert\Type('string')),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getCompletenessConstraints(): array
    {
        return [
            new Assert\Sequentially([
                new Assert\Collection([
                    'fields' => [
                        'field' => [
                            new Assert\IdenticalTo('completeness'),
                        ],
                        'operator' => [
                            new Assert\Type('string'),
                            new Assert\Choice(['=', '!=', '<', '>']),
                        ],
                        'value' => [
                            new Assert\Type('int'),
                            new Assert\Range([
                                'min' => 0,
                                'max' => 100,
                                'notInRangeMessage' => 'akeneo_catalogs.validation.product_selection.criteria.completeness.value',
                            ]),
                        ],
                        'scope' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                        'locale' => [
                            new Assert\Type('string'),
                            new Assert\NotBlank(),
                        ],
                    ],
                    'allowMissingFields' => false,
                    'allowExtraFields' => false,
                ]),
                new Assert\Callback(function (array $criterion, ExecutionContextInterface $context): void {

                    /** @var string $completenessChannel */
                    $completenessChannel = $criterion['scope'] ?? throw new \LogicException();
                    /** @var string $completenessLocale */
                    $completenessLocale = $criterion['locale'] ?? throw new \LogicException();

                    $activeChannels = $this->getChannelsQuery->execute(1, 20, $completenessChannel);
                    if (\count($activeChannels) === 0) {
                        $context->buildViolation('akeneo_catalogs.validation.product_selection.criteria.completeness.channel')
                            ->atPath('[scope]')
                            ->addViolation();

                        return;
                    }

                    $activeLocale = $activeChannels[0]['locales'];
                    $completenessLocaleIsValid = 0 < \count(\array_filter(
                        $activeLocale,
                        static fn (array $locale) => $locale['code'] === $completenessLocale
                    ));

                    if (!$completenessLocaleIsValid) {
                        $context->buildViolation('akeneo_catalogs.validation.product_selection.criteria.completeness.locale')
                            ->atPath('[locale]')
                            ->addViolation();
                    }
                }),
            ]),
        ];
    }
}
