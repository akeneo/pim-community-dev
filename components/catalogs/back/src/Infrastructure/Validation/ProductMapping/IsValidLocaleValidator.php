<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
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
 * @phpstan-type AttributeSource array{source: string, scope: string|null, locale: string|null}
 * @phpstan-type Attribute array{
 *    attribute_group_code: string,
 *    attribute_group_label: string,
 *    code: string,
 *    default_measurement_unit?: string,
 *    label: string,
 *    localizable: bool,
 *    measurement_family?: string,
 *    scopable: bool,
 *    type: string
 * }
 */
final class IsValidLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private GetLocalesQueryInterface $getLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsValidLocale) {
            throw new UnexpectedTypeException($constraint, IsValidLocale::class);
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $locales = $this->getLocalesQuery->execute();

        $exists = \count(\array_filter($locales, static fn (array $locale): bool => $locale['code'] === $value)) > 0;

        if (!$exists) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.locale.unknown')
                ->atPath('[label_locale]')
                ->addViolation();
        }
    }
}
