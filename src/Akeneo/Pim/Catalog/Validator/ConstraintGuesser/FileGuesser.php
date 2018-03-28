<?php

namespace Pim\Component\Catalog\Validator\ConstraintGuesser;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Pim\Component\Catalog\Validator\Constraints\File;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileGuesser implements ConstraintGuesserInterface
{
    /** @staticvar string */
    const MEGABYTE_UNIT = 'M';

    /** @staticvar string */
    const KILOBYTE_UNIT = 'k';

    /** @staticvar string */
    const KILOBYTE_MULTIPLIER = 1024;

    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::FILE,
                AttributeTypes::IMAGE,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];
        $options = [];

        if ($maxSize = $attribute->getMaxFileSize()) {
            if ($maxSize == (int) $maxSize) {
                $maxSize = (int) $maxSize;
                $unit = self::MEGABYTE_UNIT;
            } else {
                $maxSize = intval($maxSize * self::KILOBYTE_MULTIPLIER);
                $unit = self::KILOBYTE_UNIT;
            }
            if ($maxSize > 0) {
                $options['maxSize'] = sprintf('%d%s', $maxSize, $unit);
            }
        }

        if ($allowedExtensions = $attribute->getAllowedExtensions()) {
            $options['allowedExtensions'] = $allowedExtensions;
        }

        if ($options) {
            $constraints[] = new File($options);
        }

        return $constraints;
    }
}
