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

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\Price\Currency as CurrencyConstraint;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

final class CurrencyValidator extends ConstraintValidator
{
    public function __construct(private FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
    }

    public function validate($currencyCode, Constraint $constraint): void
    {
        if (!$constraint instanceof CurrencyConstraint) {
            throw new UnexpectedTypeException($constraint, CurrencyConstraint::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($currencyCode, [
            new Type('string'),
            new NotNull(null, CurrencyConstraint::CURRENCY_SHOULD_NOT_BE_NULL),
        ]);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $this->validateCurrency($currencyCode, $constraint);
    }

    private function validateCurrency(string $currencyCode, CurrencyConstraint $constraint): void
    {
        $hasChannel = null !== $constraint->getChannelCode();
        $currencies = $hasChannel
            ? $this->findActivatedCurrencies->forChannel($constraint->getChannelCode())
            : $this->findActivatedCurrencies->forAllChannels();

        if (!in_array($currencyCode, $currencies)) {
            $message = $hasChannel ? CurrencyConstraint::CURRENCY_SHOULD_BE_ACTIVE_ON_CHANNEL : CurrencyConstraint::CURRENCY_SHOULD_BE_ACTIVE;

            $this->context->buildViolation(
                $message,
                [
                    '{{ currency_code }}' => $currencyCode,
                    '{{ channel_code }}' => $constraint->getChannelCode(),
                ],
            )
                ->addViolation();
        }
    }
}
