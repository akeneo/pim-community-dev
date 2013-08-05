<?php
namespace Pim\Bundle\ProductBundle\Validator;

use Symfony\Component\Validator\ExecutionContext;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Validator class for ProductAttribute entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
     * Validate ProductAttribute entity
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    public static function isValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        self::areOptionsValid($productAttribute, $context);
        self::isDefaultValueValid($productAttribute, $context);
    }

    /**
     * Validation rule for attribute option values
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function areOptionsValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        $optionTypes = array(
            'pim_product_multiselect',
            'pim_product_simpleselect'
        );

        if (in_array($productAttribute->getAttributeType(), $optionTypes)) {
            $existingValues = array();
            foreach ($productAttribute->getOptions() as $option) {
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
    }

    /**
     * Validation rule for defaultValue
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     *
     * @return void
     */
    protected static function isDefaultValueValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        $value = $productAttribute->getDefaultValue();
        if ($value !== null) {
            switch ($productAttribute->getAttributeType()) {
                case 'pim_product_date':
                    if (!$value instanceof \Datetime) {
                        break;
                    }
                    if ($min = $productAttribute->getDateMin()) {
                        if ($min->getTimestamp() > $value->getTimestamp()) {
                            break;
                        }
                    }
                    if ($max = $productAttribute->getDateMax()) {
                        if ($max->getTimestamp() < $value->getTimestamp()) {
                            break;
                        }
                    }

                    return;
                case 'pim_product_price_collection':
                case 'pim_product_number':
                case 'pim_product_metric':
                    if ($productAttribute->isNegativeAllowed() === false && $value < 0) {
                        break;
                    }
                    if ($productAttribute->getNumberMin() !== null && $value < $productAttribute->getNumberMin()) {
                        break;
                    }
                    if ($productAttribute->getNumberMax() !== null && $value > $productAttribute->getNumberMax()) {
                        break;
                    }
                    if (!$productAttribute->isDecimalsAllowed() && $value != (int) $value) {
                        break;
                    }

                    return;
                case 'pim_product_textarea':
                    if ($productAttribute->getMaxCharacters() !== null) {
                        if (strlen($value) > $productAttribute->getMaxCharacters()) {
                            break;
                        }
                    }

                    return;
                case 'pim_product_text':
                    if ($productAttribute->getMaxCharacters() !== null) {
                        if (strlen($value) > $productAttribute->getMaxCharacters()) {
                            break;
                        }
                    }
                    if ($productAttribute->getValidationRule() == 'regexp') {
                        if (@preg_match($productAttribute->getValidationRegexp(), $value)) {
                            return;
                        }
                        break;
                    }
                    if ($productAttribute->getValidationRule() == 'email') {
                        $validator = new \Symfony\Component\Validator\Constraints\EmailValidator();
                        $validator->initialize($context);
                        $validator->validate($value, new \Symfony\Component\Validator\Constraints\Email());

                        return;
                    }
                    if ($productAttribute->getValidationRule() == 'url') {
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
            $context->addViolation(self::VIOLATION_INVALID_DEFAULT_VALUE);
        }
    }
}
