<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Validator;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface as NewConstraintGuesserInterface;

/**
 * Constraint guesser interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Deprecated since version 1.1, to be removed in 1.2. Use CatalogBundle/ConstraintGuesserInterface
 */
interface ConstraintGuesserInterface extends NewConstraintGuesserInterface
{
}
