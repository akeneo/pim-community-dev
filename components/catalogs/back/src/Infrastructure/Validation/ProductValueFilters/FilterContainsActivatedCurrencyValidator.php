<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductValueFilters;

use Akeneo\Catalogs\Application\Persistence\Currency\IsCurrencyActivatedQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FilterContainsActivatedCurrencyValidator extends ConstraintValidator
{
    public function __construct(
        private IsCurrencyActivatedQueryInterface $isCurrencyActivated,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FilterContainsActivatedCurrency) {
            throw new UnexpectedTypeException($constraint, FilterContainsActivatedCurrency::class);
        }

        if (!\is_string($value) || '' === $value) {
            return;
        }

        $isActivated = $this->isCurrencyActivated->execute($value);

        if (!$isActivated) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_value_filters.currencies.unknown',
                    ['{{ currency_name }}' => $value],
                )
                ->addViolation();
        }
    }
}
