<?php

namespace Pim\Bundle\ProductBundle\Validator\ConstraintGuesser;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\ProductBundle\Validator\Constraints\File;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileGuesser implements ConstraintGuesserInterface
{
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return AbstractAttributeType::BACKEND_TYPE_MEDIA === $attribute->getBackendType();
    }

    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();
        $options     = array();

        if ($attribute->getMaxFileSize()) {
            $options['maxSize'] = $attribute->getMaxFileSize();
        }

        if ($allowedFileExtensions = $attribute->getAllowedFileExtensions()) {
            $options['allowedExtensions'] = $allowedFileExtensions;
        }

        if ($options) {
            $constraints[] = new File($options);
        }

        return $constraints;
    }
}

