<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
     * @param AttributeInterface|MetricInterface|ValueInterface $object
     * @param Constraint                                        $constraint
     */
    public function validate($object, Constraint $constraint)
    {
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
