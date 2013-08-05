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
     * Global scope value
     * @staticvar integer
     */
    const GLOBAL_SCOPE_VALUE = 0;

    /**
     * Violation message for missing attribute code
     * @staticvar string
     */
    const VIOLATION_MISSING_CODE = 'Please specify attribute name';

    /**
     * Violation message for invalid attribute code
     * @staticvar string
     */
    const VIOLATION_INVALID_CODE = 'Attribute name may contain only letters, numbers and underscores';

    /**
     * Violation message for unique attribute with incorrect scope and accepting translations
     * @staticvar string
     */
    const VIOLATION_UNIQUE_SCOPE_I18N = 'Unique attribute results in used of Global scope and no translations';

    /**
     * Violation message for unique attribute with incorrect attribute type
     * @var unknown_type
     */
    const VIOLATION_UNIQUE_ATT_TYPE = 'Unique attribute is forbidden for this attribute type';

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
     * Violation messages for invalid custom attribute values
     * @staticvar string
     */
    const VIOLATION_INVALID_MAX_CHARACTERS = 'The maximum characters property is not valid';
    const VIOLATION_INVALID_VALIDATION_RULE = 'The validation rule property is not valid';
    const VIOLATION_INVALID_VALIDATION_REGEXP = 'The validation regular expression is not valid';
    const VIOLATION_INVALID_NUMBER_MIN = 'The minimum number is not valid';
    const VIOLATION_INVALID_NUMBER_MAX = 'The maximum number is not valid';
    const VIOLATION_INVALID_DATE_TYPE = 'The date type is not valid';
    const VIOLATION_INVALID_DATE_MIN = 'The minumum date is not valid';
    const VIOLATION_INVALID_DATE_MAX = 'The maximum date is not valid';
    const VIOLATION_INVALID_ALLOWED_FILE_SOURCES = 'The allowed file sources property is not valid';
    const VIOLATION_INVALID_MAX_FILE_SIZE = 'The maximum file size property is not valid';
    const VIOLATION_INVALID_DEFAULT_VALUE = 'The default value is not valid';
    const VIOLATION_DEFAULT_VALUE_DISABLED = 'No default value may be specified for this attribute type';
    const VIOLATION_VALIDATION_REGEXP_DISABLED = 'Regular expression may not be specified for this validation rule';

    /**
     * Validate ProductAttribute entity
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    public static function isValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        self::isCodeValid($productAttribute, $context);
        self::isAttributeTypeMatrixValid($productAttribute, $context);
        self::isUniqueConstraintValid($productAttribute, $context);
        self::areOptionsValid($productAttribute, $context);
        self::arePropertiesValid($productAttribute, $context);
    }

    /**
     * Validation rule for attribute code
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isCodeValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if (!$productAttribute->getCode()) {
            $context->addViolation(self::VIOLATION_MISSING_CODE);
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $productAttribute->getCode())) {
            $context->addViolation(self::VIOLATION_INVALID_CODE);
        }
    }

    /**
     * Validation rules for attribute type value
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isAttributeTypeMatrixValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        $attributeType = array(
            'pim_product_textarea',
            'pim_product_price_collection',
            'pim_product_multiselect',
            'pim_product_simpleselect',
            'pim_product_image',
            'pim_product_file',
            'pim_product_metric',
            'pim_product_boolean'
        );
        if (in_array($productAttribute->getAttributeType(), $attributeType)
                && $productAttribute->getUnique() === true) {
            $context->addViolation(self::VIOLATION_UNIQUE_ATT_TYPE);
        }
    }

    /**
     * Validation rule for unique attribute
     * If a product attribute is unique, scope value must be Global and product attribute mustn't be translatable
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isUniqueConstraintValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($productAttribute->getUnique() === true) {
            if ($productAttribute->getScopable() != false
                    || $productAttribute->getTranslatable() === true) {
                $context->addViolation(self::VIOLATION_UNIQUE_SCOPE_I18N);
            }
        }
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
     * Validation rule for attribute properties
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function arePropertiesValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        self::isDefaultValueValid($productAttribute, $context);

        switch($productAttribute->getAttributeType()) {
            case 'pim_product_date':
                self::isDateTypeValid($productAttribute, $context);
                self::isDateMinValid($productAttribute, $context);
                self::isDateMaxValid($productAttribute, $context);
                break;
            case 'pim_product_price_collection':
            case 'pim_product_number':
            case 'pim_product_metric':
                self::isNumberMinValid($productAttribute, $context);
                self::isNumberMaxValid($productAttribute, $context);
                break;
            case 'pim_product_textarea':
                self::isMaxCharactersValid($productAttribute, $context);
                break;
            case 'pim_product_file':
            case 'pim_product_image':
                self::isAllowedFileSourcesValid($productAttribute, $context);
                self::isMaxFileSizeValid($productAttribute, $context);
                break;
            case 'pim_product_text':
            case 'pim_product_identifier':
                self::isMaxCharactersValid($productAttribute, $context);
                self::isValidationRuleValid($productAttribute, $context);
                break;
            default:
                break;
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
            $exclusions = array(
                    'pim_product_image',
                    'pim_product_file',
                    'pim_product_identifier',
            );

            if (in_array($productAttribute->getAttributeType(), $exclusions)) {
                $context->addViolation(self::VIOLATION_DEFAULT_VALUE_DISABLED);

                return;
            }

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

    /**
     * Validation rule for maxCharacters
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isMaxCharactersValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        $value = $productAttribute->getMaxCharacters();
        if ($value !== null) {
            if (gettype($value) != 'integer' || $value <= 0) {
                $context->addViolation(self::VIOLATION_INVALID_MAX_CHARACTERS);
            }
        }
    }

    /**
     * Validation rule for validationRule
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isValidationRuleValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($value = $productAttribute->getValidationRule()) {
            $validRules = array(
                    'url', 'email', 'regexp'
            );
            if (!in_array($value, $validRules)) {
                $context->addViolation(self::VIOLATION_INVALID_VALIDATION_RULE);
            }
            self::isValidationRegexpValid($productAttribute, $context);
        }
    }

    /**
     * Validation rule for validationRegexp
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isValidationRegexpValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($productAttribute->getValidationRule() == 'regexp') {
            if (@preg_match($productAttribute->getValidationRegexp(), null) === false) {
                $context->addViolation(self::VIOLATION_INVALID_VALIDATION_REGEXP);
            }
        } elseif ($productAttribute->getValidationRegexp()) {
            $context->addViolation(self::VIOLATION_VALIDATION_REGEXP_DISABLED);
        }
    }

    /**
     * Validation rule for numberMin
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isNumberMinValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($value = $productAttribute->getNumberMin()) {
            if ($productAttribute->isNegativeAllowed()) {
                if ($value == (int) $value || $productAttribute->isDecimalsAllowed()) {
                    return;
                }
            } elseif (($value == (int) $value || $productAttribute->isDecimalsAllowed()) && $value >= 0) {
                return;
            }

            $context->addViolation(self::VIOLATION_INVALID_NUMBER_MIN);
        }
    }

    /**
     * Validation rule for numberMax
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isNumberMaxValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($value = $productAttribute->getNumberMax()) {
            if ($productAttribute->getNumberMax() > $productAttribute->getNumberMin()) {
                if ($productAttribute->isNegativeAllowed()) {
                    if ($value == (int) $value || $productAttribute->isDecimalsAllowed()) {
                        return;
                    }
                } elseif (($value == (int) $value || $productAttribute->isDecimalsAllowed()) && $value >= 0) {
                    return;
                }
            }

            $context->addViolation(self::VIOLATION_INVALID_NUMBER_MAX);
        }
    }

    /**
     * Validation rule for dateType
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isDateTypeValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if (!in_array($productAttribute->getDateType(), array('date', 'time', 'datetime'))) {
            $context->addViolation(self::VIOLATION_INVALID_DATE_TYPE);
        }
    }

    /**
     * Validation rule for dateMin
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isDateMinValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($productAttribute->getDateMin()) {
            if (!$productAttribute->getDateMin() instanceof \DateTime) {
                $context->addViolation(self::VIOLATION_INVALID_DATE_MIN);
            }
        }
    }

    /**
     * Validation rule for dateMax
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isDateMaxValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        $dateMax = $productAttribute->getDateMax();
        $dateMin = $productAttribute->getDateMin();

        if ($dateMax) {
            if (!$dateMax instanceof \DateTime || ($dateMin && $dateMin->getTimestamp() >= $dateMax->getTimestamp())) {
                $context->addViolation(self::VIOLATION_INVALID_DATE_MAX);
            }
        }
    }

    /**
     * Validation rule for allowedFileSources
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isAllowedFileSourcesValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        $sources = $productAttribute->getAllowedFileSources();
        if (!empty($sources)) {
            if (!in_array($sources, array('all', 'upload', 'external'))) {
                $context->addViolation(self::VIOLATION_INVALID_ALLOWED_FILE_SOURCES);
            }
        }
    }

    /**
     * Validation rule for maxFileSize
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isMaxFileSizeValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($productAttribute->getMaxFileSize()) {
            if (gettype($productAttribute->getMaxFileSize()) != 'integer' || $productAttribute->getMaxFileSize() < 0) {
                $context->addViolation(self::VIOLATION_INVALID_MAX_FILE_SIZE);
            }
        }
    }
}
