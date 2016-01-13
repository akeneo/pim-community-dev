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
 * Validation constraint on a field.
 *
 * @deprecated will be removed in 1.6 please use ExistingFilterField, ExistingAddField, ExistingSetField or
 *             ExistingCopierField
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ExistingField extends Constraint
{
    /** @var string */
    public $message = 'The field "%field%" does not exist.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_field_validator';
    }
}
