<?php

namespace Pim\Bundle\CatalogBundle\Validator;

use Symfony\Component\Validator\ExecutionContext;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Validator for options and default value of AttributeInterface entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValidator
{
    /**
     * Violation message for missing code of attribute option
     * @staticvar string
     */
    const VIOLATION_OPTION_CODE_REQUIRED = 'Code must be specified for all options';

    /**
     * Violation message for duplicate code of attribute option
     * @staticvar string
     */
    const VIOLATION_DUPLICATE_OPTION_CODE = 'Code must be different for each option';

    /**
     * Validation rule for attribute option values
     *
     * @param AttributeInterface $attribute
     * @param ExecutionContext   $context
     *
     * @static
     */
    public static function areOptionsValid(AttributeInterface $attribute, ExecutionContext $context)
    {
        $existingValues = array();
        foreach ($attribute->getOptions() as $option) {
            if (in_array($option->getCode(), $existingValues)) {
                $context->addViolation(self::VIOLATION_DUPLICATE_OPTION_CODE);
            }
            if ($option->getCode() === null) {
                $context->addViolation(self::VIOLATION_OPTION_CODE_REQUIRED);
            } else {
                $existingValues[] = $option->getCode();
            }
        }
    }
}
