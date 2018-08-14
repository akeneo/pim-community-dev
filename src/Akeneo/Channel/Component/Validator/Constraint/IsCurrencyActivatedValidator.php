<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Channel\Component\Model\CurrencyInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsCurrencyActivatedValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($currency, Constraint $constraint)
    {
        if (null === $currency) {
            return;
        }

        if ($currency instanceof CurrencyInterface && !$currency->isActivated()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%currency%', $currency->getCode())
                ->addViolation();
        }
    }
}
