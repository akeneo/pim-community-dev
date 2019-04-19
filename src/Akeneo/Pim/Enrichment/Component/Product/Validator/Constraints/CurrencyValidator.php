<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
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
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var array */
    protected $currencyCodes;

    /**
     * @param CurrencyRepositoryInterface $currencyRepository
     */
    public function __construct(CurrencyRepositoryInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
        $this->currencyCodes = [];
    }

    /**
     * Validate currency of a price
     *
     * @param mixed      $object
     * @param Constraint $constraint
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof Currency) {
            throw new UnexpectedTypeException($constraint, Currency::class);
        }

        if ($object instanceof ProductPriceInterface) {
            if (!in_array($object->getCurrency(), $this->getCurrencyCodes())) {
                $this->context->buildViolation($constraint->unitMessage)
                    ->atPath('currency')
                    ->addViolation();
            }
        }
    }

    /**
     * @return array
     */
    protected function getCurrencyCodes()
    {
        if (empty($this->currencyCodes)) {
            $this->currencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();
        }

        return $this->currencyCodes;
    }
}
