<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\Validator;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConstraintGuesserInterface
{
    public function supportAttribute(AbstractAttribute $attribute);
    public function guessConstraints(AbstractAttribute $attribute);
}

