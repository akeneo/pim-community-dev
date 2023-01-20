<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters;

use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesByCodeQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FilterContainsActivatedLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private GetLocalesByCodeQueryInterface $getLocalesByCodeQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FilterContainsActivatedLocale) {
            throw new UnexpectedTypeException($constraint, FilterContainsActivatedLocale::class);
        }

        if (!\is_string($value) || '' === $value) {
            return;
        }

        /** @var array<LocaleInterface> $locales< */
        $locales = $this->getLocalesByCodeQuery->execute([$value]);

        if ([] === $locales) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_value_filters.locale.unknown',
                    ['{{ locale_name }}' => $value],
                )
                ->addViolation();
        }
    }
}
