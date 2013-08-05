<?php
namespace Pim\Bundle\ProductBundle\Validator;

use Symfony\Component\Validator\ExecutionContext;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Validator for options and default value of ProductAttribute entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeValidator
{
    /**
     * Violation message for missing default value of attribute option
     * @staticvar string
     */
    const VIOLATION_OPTION_DEFAULT_VALUE_REQUIRED = 'Default value must be specified for all options';

    /**
     * Violation message for duplicate default value of attribute option
     * @staticvar string
     */
    const VIOLATION_DUPLICATE_OPTION_DEFAULT_VALUE = 'Default value must be different for each option';

    /**
     * Violation message for invalid default value
     * @staticvar string
     */
    const VIOLATION_INVALID_DEFAULT_VALUE = 'The default value is not valid';

    /**
     * Validation rule for attribute option values
     *
     * @param ProductAttribute $attribute
     * @param ExecutionContext $context
     *
     * @static
     */
    public static function areOptionsValid(ProductAttribute $attribute, ExecutionContext $context)
    {
        $existingValues = array();
        foreach ($attribute->getOptions() as $option) {
            if (in_array($option->getDefaultValue(), $existingValues)) {
                $context->addViolation(self::VIOLATION_DUPLICATE_OPTION_DEFAULT_VALUE);
            }
            if ($option->getDefaultValue() === null) {
                $context->addViolation(self::VIOLATION_OPTION_DEFAULT_VALUE_REQUIRED);
            } else {
                $existingValues[] = $option->getDefaultValue();
            }
        }
    }

    /**
     * Validation rule for defaultValue
     *
     * @param ProductAttribute $attribute
     * @param ExecutionContext $context
     *
     * @static
     *
     * @return void
     */
    public static function isDefaultValueValid(ProductAttribute $attribute, ExecutionContext $context)
    {
        $value = $attribute->getDefaultValue();
        if ($value !== null) {
            switch ($attribute->getAttributeType()) {
                case 'pim_product_date':
                    if (!$value instanceof \Datetime) {
                        break;
                    }
                    if ($min = $attribute->getDateMin()) {
                        if ($min->getTimestamp() > $value->getTimestamp()) {
                            break;
                        }
                    }
                    if ($max = $attribute->getDateMax()) {
                        if ($max->getTimestamp() < $value->getTimestamp()) {
                            break;
                        }
                    }

                    return;
                case 'pim_product_price_collection':
                case 'pim_product_number':
                case 'pim_product_metric':
                    if ($attribute->isNegativeAllowed() === false && $value < 0) {
                        break;
                    }
                    if ($attribute->getNumberMin() !== null && $value < $attribute->getNumberMin()) {
                        break;
                    }
                    if ($attribute->getNumberMax() !== null && $value > $attribute->getNumberMax()) {
                        break;
                    }
                    if (!$attribute->isDecimalsAllowed() && $value != (int) $value) {
                        break;
                    }

                    return;
                case 'pim_product_textarea':
                    if ($attribute->getMaxCharacters() !== null) {
                        if (strlen($value) > $attribute->getMaxCharacters()) {
                            break;
                        }
                    }

                    return;
                case 'pim_product_text':
                    if ($attribute->getMaxCharacters() !== null) {
                        if (strlen($value) > $attribute->getMaxCharacters()) {
                            break;
                        }
                    }
                    if ($attribute->getValidationRule() == 'regexp') {
                        if (@preg_match($attribute->getValidationRegexp(), $value)) {
                            return;
                        }
                        break;
                    }
                    if ($attribute->getValidationRule() == 'email') {
                        $validator = new \Symfony\Component\Validator\Constraints\EmailValidator();
                        $validator->initialize($context);
                        $validator->validate($value, new \Symfony\Component\Validator\Constraints\Email());

                        return;
                    }
                    if ($attribute->getValidationRule() == 'url') {
                        $validator = new \Symfony\Component\Validator\Constraints\UrlValidator();
                        $validator->initialize($context);
                        $validator->validate($value, new \Symfony\Component\Validator\Constraints\Url());

                        return;
                    }

                    return;
                case 'pim_product_boolean':
                    if ($value !== (bool) $value) {
                        break;
                    }

                    return;
                default:
                    return;
            }
            $context->addViolationAt('defaultValue', self::VIOLATION_INVALID_DEFAULT_VALUE);
        }
    }
}
