<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint checking that a field is removable.
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ExistingRemoveField extends Constraint
{
    /** @var string */
    public $message = 'You cannot remove items from the "%field%" field.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_remove_field_validator';
    }
}
