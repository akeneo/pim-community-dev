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
class RegexGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            [
                'pim_catalog_text',
                'pim_catalog_identifier',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = [];

        if ('regexp' === $attribute->getValidationRule() && $pattern = $attribute->getValidationRegexp()) {
            $constraints[] = new Assert\Regex(['pattern' => $pattern]);
        }

        return $constraints;
    }
}
