<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
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
    /** @var CurrencyManager */
    protected $currencyManager;

    /** @var array */
    protected $currencyCodes;

    /**
     * @param CurrencyManager $currencyManager
     */
    public function __construct(CurrencyManager $currencyManager)
    {
        $this->currencyManager = $currencyManager;
        $this->currencyCodes = [];
    }

    /**
     * Validate currency of a price
     *
     * @param AttributeInterface|MetricInterface|ProductValueInterface $object
     * @param Constraint                                               $constraint
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
            $this->currencyCodes = $this->currencyManager->getActiveCodes();
        }

        return $this->currencyCodes;
    }
}
