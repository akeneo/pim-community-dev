<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Numeric;

/**
 * Guesser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumericGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            [
                'pim_catalog_metric',
                'pim_catalog_number',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = [];

        if (!$attribute->isDecimalsAllowed()) {
            $constraints[] = new Numeric();
        }

        return $constraints;
    }
}
