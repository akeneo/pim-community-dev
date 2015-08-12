<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Class IdentifierGuesser
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return 'pim_catalog_identifier' === $attribute->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        return [
            new Regex([
                'pattern' => '/[,;]/',
                'match'   => false,
                'message' => 'regex.comma_or_semicolon.message',
            ])
        ];
    }
}
