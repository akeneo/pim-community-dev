<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsRootCategory extends Constraint
{
    /** @var string */
    public $message = 'The category "%category%" has to be a root category.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_is_root_category_validator';
    }
}
