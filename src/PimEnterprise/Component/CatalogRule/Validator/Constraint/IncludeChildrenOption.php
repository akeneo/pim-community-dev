<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint checking the 'include_children' option of a remove action
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class IncludeChildrenOption extends Constraint
{
    public $invalidFieldMessage = 'The "include_children" option can only be applied with field "categories", ' .
        '"%field%" given';
    public $invalidTypeMessage = 'The "include_children" option is expected to be of type "bool", "%type%" given.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_include_children_option_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
