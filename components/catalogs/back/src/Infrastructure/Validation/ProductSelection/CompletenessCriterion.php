<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\GetChannelLocalesQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CompletenessCriterion extends Compound
{
    public function __construct(private GetChannelLocalesQueryInterface $getChannelLocalesQuery)
    {
        parent::__construct();
    }

    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<array-key, Constraint>
     */
    protected function getConstraints(array $options = []): array
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

                    try {
                        $activeLocales = $this->getChannelLocalesQuery->execute($completenessChannel);
                    } catch (\LogicException) {
                        $context->buildViolation('akeneo_catalogs.validation.product_selection.criteria.completeness.channel')
                            ->atPath('[scope]')
                            ->addViolation();

                        return;
                    }

                    $completenessLocaleIsValid = 0 < \count(\array_filter(
                        $activeLocales,
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
