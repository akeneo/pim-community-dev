<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Price attribute validator
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyValidator extends ConstraintValidator
{
    protected FindActivatedCurrenciesInterface $findActivatedCurrencies;
    protected array $currencyCodes;

    public function __construct(FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
        $this->findActivatedCurrencies = $findActivatedCurrencies;
        $this->currencyCodes = [];
    }

    /**
     * Validate currency of a price
     *
     * @param mixed      $object
     * @param Constraint $constraint
     */
    public function validate($object, Constraint $constraint): void
    {
        if (!$constraint instanceof Currency) {
            throw new UnexpectedTypeException($constraint, Currency::class);
        }

        if ($object instanceof ProductPriceInterface) {
            if (!in_array($object->getCurrency(), $this->getCurrencyCodes())) {
                $attributeCode = $this->context->getObject() instanceof AbstractValue ?
                    $this->context->getObject()->getAttributeCode()
                    : '';

                $this->context->buildViolation($constraint->message, [
                    '%attribute_code%' => $attributeCode,
                    '%currency_code%' => $object->getCurrency(),
                ])
                    ->atPath('currency')
                    ->setCode(Currency::CURRENCY)
                    ->addViolation();
            }
        }
    }

    /**
     * @return array
     */
    protected function getCurrencyCodes(): array
    {
        if (empty($this->currencyCodes)) {
            $this->currencyCodes = $this->findActivatedCurrencies->forAllChannels();
        }

        return $this->currencyCodes;
    }
}
