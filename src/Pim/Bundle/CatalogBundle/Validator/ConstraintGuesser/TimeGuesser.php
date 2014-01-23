<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Symfony\Component\Validator\Constraints\Time;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;

/**
 * Time guesser
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TimeGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            [
                'pim_catalog_date',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = [];

        if ($attribute->getDateType() === 'time') {
            $constraints[] = new Time();
        }

        return $constraints;
    }
}
