<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\CompletenessCriterion;

use Akeneo\Catalogs\Infrastructure\Persistence\GetChannelLocalesQuery;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CompletenessCriterionValuesValidator extends ConstraintValidator
{
    public function __construct(
        private GetChannelLocalesQuery $getChannelLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var array{field: string, operator: string, value: int, scope: string, locale: string} $value */

        if (!$constraint instanceof CompletenessCriterionValues) {
            throw new UnexpectedTypeException($constraint, CompletenessCriterionValues::class);
        }

        try {
            $activeLocales = $this->getChannelLocalesQuery->execute($value['scope']);
        } catch (\LogicException) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();

            return;
        }

        $localeIsValid = 0 < \count(
            \array_filter(
                $activeLocales,
                static fn (array $locale) => $locale['code'] === $value['locale']
            )
        );

        if (!$localeIsValid) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.disabled')
                ->atPath('[locale]')
                ->addViolation();
        }
    }
}
