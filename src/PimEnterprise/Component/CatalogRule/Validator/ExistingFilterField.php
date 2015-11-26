<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint on a filter field.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingFilterField extends Constraint
{
    /** @var string */
    public $message = 'The field "%field%" cannot be filtered.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_constraint_filter_field_validator';
    }
}
