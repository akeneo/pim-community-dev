<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint on a field on which you want to set new data.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingSetField extends Constraint
{
    /** @var string */
    public $message = 'You cannot set data to the "%field%" field.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_set_field_validator';
    }
}
