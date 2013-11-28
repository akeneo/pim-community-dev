<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LengthGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            array(
                'pim_catalog_text',
                'pim_catalog_textarea',
                'pim_catalog_identifier',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if ($maxCharacters = $attribute->getMaxCharacters()) {
            $constraints[] = new Assert\Length(array('max' => $maxCharacters));
        }

        return $constraints;
    }
}
