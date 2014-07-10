<?php

namespace Pim\Bundle\CatalogBundle\Validator;

use Symfony\Component\Validator\ExecutionContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Validator for options and default value of AbstractAttribute entity
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
    const VIOLATION_OPTION_CODE_REQUIRED = 'Code must be specified for all options.';

    /**
     * Violation message for duplicate code of attribute option
     * @staticvar string
     */
    const VIOLATION_DUPLICATE_OPTION_CODE = 'Code must be different for each option.';

    /**
     * Violation message for non-string code
     * @staticvar string
     */
    const VIOLATION_NON_STRING_CODE = 'Code must be a string.';

    /**
     * Validation rule for attribute option values
     *
     * @param AbstractAttribute $attribute
     * @param ExecutionContext  $context
     */
    public static function areOptionsValid(AbstractAttribute $attribute, ExecutionContext $context)
    {
        $existingValues = array();
        foreach ($attribute->getOptions() as $option) {
            $code = $option->getCode();

            if (isset($existingValues[$code])) {
                $context->addViolation(self::VIOLATION_DUPLICATE_OPTION_CODE);
            } elseif ($code === null) {
                $context->addViolation(self::VIOLATION_OPTION_CODE_REQUIRED);
            } elseif (!is_string($code)) {
                $context->addViolation(
                    self::VIOLATION_NON_STRING_CODE.
                    sprintf(' Type "%s" found.', gettype($code))
                );
            } else {
                $existingValues[$code] = '';
            }
        }
    }
}
