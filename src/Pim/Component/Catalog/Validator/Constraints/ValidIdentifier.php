<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Check that the identifier is valid
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidIdentifier extends Constraint
{
    public $message = 'pim_catalog.constraint.valid_identifier';

    /***
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'valid_identifier';
    }
}
