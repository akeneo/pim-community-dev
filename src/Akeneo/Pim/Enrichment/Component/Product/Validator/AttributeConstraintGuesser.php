<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Attribute constraint guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeConstraintGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute): array
    {
        $constraints = [];

        if ($attribute->isRequired()) {
            $constraints[] = new NotBlank();
        }

        switch ($attribute->getBackendType()) {
            case AttributeTypes::BACKEND_TYPE_DATE:
                $constraints[] = new Date();
                break;
            case AttributeTypes::BACKEND_TYPE_DATETIME:
                $constraints[] = new DateTime();
                break;
        }

        return $constraints;
    }
}
