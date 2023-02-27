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
 */
final class IsActivatedLocaleValidator extends ConstraintValidator
{
    public function __construct(
        private GetLocalesQueryInterface $getLocalesQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsActivatedLocale) {
            throw new UnexpectedTypeException($constraint, IsActivatedLocale::class);
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
