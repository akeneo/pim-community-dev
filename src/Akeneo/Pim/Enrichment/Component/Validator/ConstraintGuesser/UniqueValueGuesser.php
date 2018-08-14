<?php

namespace Akeneo\Pim\Enrichment\Component\Validator\ConstraintGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pim\Component\Catalog\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Validator\Constraints\UniqueValue;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        $availableTypes = [
            AttributeTypes::BACKEND_TYPE_TEXT,
            AttributeTypes::BACKEND_TYPE_DATE,
            AttributeTypes::BACKEND_TYPE_DATETIME,
            AttributeTypes::BACKEND_TYPE_DECIMAL
        ];

        return in_array($attribute->getBackendType(), $availableTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];

        // We don't apply the unique value constraint on identifier because it is done
        // by `Akeneo\Pim\Enrichment\Component\Validator\Constraints\Product\UniqueProductEntity`
        if ($attribute->isUnique() && AttributeTypes::IDENTIFIER !== $attribute->getType()) {
            $constraints[] = new UniqueValue();
        }

        return $constraints;
    }
}
