<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Range as BaseRange;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Range extends BaseRange
{
    public $minDateMessage = 'This date should be {{ limit }} or after.';
    public $maxDateMessage = 'This date should be {{ limit }} or before.';
    public $invalidDateMessage = 'This value is not a valid date.';

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        if (isset($options['min']) && is_numeric($options['min'])) {
            $options['min'] = floatval($options['min']);
        }
        if (isset($options['max']) && is_numeric($options['max'])) {
            $options['max'] = floatval($options['max']);
        }

        parent::__construct($options);
    }
}
