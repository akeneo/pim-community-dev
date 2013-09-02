<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimal;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            array(
                'pim_catalog_price_collection',
                'pim_catalog_metric',
                'pim_catalog_number',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if (!$attribute->isDecimalsAllowed()) {
            $constraints[] = new NotDecimal();
        }

        return $constraints;
    }
}
