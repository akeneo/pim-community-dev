<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AttributeCriterion array{scope: string, locale: string}
 */
final class CriterionContainsScopeAndLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private GetChannelQueryInterface $getChannelQuery,
        private GetChannelLocalesQueryInterface $getChannelLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CriterionContainsScopeAndLocale) {
            throw new UnexpectedTypeException($constraint, CriterionContainsScopeAndLocale::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var AttributeCriterion $value */

        $channel = $this->getChannelQuery->execute($value['scope']);

        if (null === $channel) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.channel.unknown')
                ->atPath('[scope]')
                ->addViolation();

            return;
        }

        $locales = $this->getChannelLocalesQuery->execute($value['scope']);

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value['locale'])) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.locale.disabled')
                ->atPath('[locale]')
                ->addViolation();
        }
    }
}
