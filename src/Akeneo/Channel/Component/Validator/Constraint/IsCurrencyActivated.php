<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsCurrencyActivated extends Constraint
{
    /** @var string */
    public $message = 'The currency "%currency%" has to be activated.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_is_currency_activated_validator';
    }
}
