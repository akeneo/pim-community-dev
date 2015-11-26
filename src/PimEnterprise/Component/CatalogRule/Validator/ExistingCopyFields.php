<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint to check if the fromField and the toField have an existing copier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingCopyFields extends Constraint
{
    /** @var string */
    public $message = 'You cannot copy data from "%fromField%" field to the "%toField%" field.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_constraint_copy_fields_validator';
    }
}
