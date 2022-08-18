<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\Price;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\Price\Currency as CurrencyConstraint;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\FindCurrency;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\Currency;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CurrencyValidator extends ConstraintValidator
{
    public function __construct(private FindCurrency $findCurrency)
    {
    }

    public function validate($currencyCode, Constraint $constraint): void
    {
        if (!$constraint instanceof CurrencyConstraint) {
            throw new UnexpectedTypeException($constraint, CurrencyConstraint::class);
        }

        $currency = $this->findCurrency->byCode($currencyCode);

        if (!$currency instanceof CurrencyInterface) {
            $this->context->buildViolation(
                CurrencyConstraint::CURRENCY_SHOULD_EXIST,
                [
                    '{{ currency_code }}' => $currencyCode,
                ],
            )
            ->addViolation();
        }
    }
}
