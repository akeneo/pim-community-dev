<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Boolean constraint
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Boolean extends Constraint
{
    public const NOT_BOOLEAN_ERROR = '541d44c2-0cec-4b43-87f7-5df101b2a951';

    /** @var string */
    public $message = 'The {{ attribute_code }} attribute requires a boolean value (true or false) as data, a {{ given_type }} was detected.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
