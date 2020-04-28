<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ActiveCurrency;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class ActiveCurrencyValidator extends ConstraintValidator
{
    /** @var FindActivatedCurrenciesInterface */
    private $findActivatedCurrencies;

    public function __construct(FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
        $this->findActivatedCurrencies = $findActivatedCurrencies;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ActiveCurrency::class);
        if (null === $value || !is_string($value)) {
            return;
        }

        if (!in_array($value, $this->findActivatedCurrencies->forAllChannels())) {
            $this->context->buildViolation(
                $constraint->message,
                ['%currency%' => $value]
            )->addViolation();
        }
    }
}
