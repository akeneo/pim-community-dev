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
    /** @var string */
    public $minDateMessage = 'The {{ attribute_code }} attribute requires a date that should be {{ limit }} or after.';

    /** @var string */
    public $maxDateMessage = 'The {{ attribute_code }} attribute requires a date that should be {{ limit }} or before.';

    /** @var string */
    public $invalidMessage = 'The {{ attribute }} attribute requires a number, and the submitted {{ value }} value is not.';

    /** @var string */
    public $attributeCode = '';

    /** @var string */
    public $minMessage = 'The %attribute% attribute requires an equal or greater than %min_value% value.';

    /** @var string */
    public $maxMessage = 'The %attribute% attribute requires an equal or lesser than %max_value% value.';

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
