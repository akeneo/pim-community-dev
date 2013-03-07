<?php
namespace Pim\Bundle\ProductBundle\Validator;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

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
    const VIOLATION_MISSING_CODE = 'Please specify attribute code';

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
     * Violation message for disabled default value of attribute
     * @staticvar string
     */
    const VIOLATION_DEFAULT_VALUE_DISABLED = 'No default value may be specified for this attribute type';

    /**
     * Violation message for missing default value of attribute option
     * @staticvar string
     */
    const VIOLATION_OPTION_DEFAULT_VALUE_REQUIRED = 'Default label must be specified for all options';

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
        self::isDefaultValueValid($productAttribute, $context);
        self::areOptionsValid($productAttribute, $context);
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
            AbstractAttributeType::TYPE_TEXTAREA_CLASS,
            AbstractAttributeType::TYPE_MONEY_CLASS,
            AbstractAttributeType::TYPE_OPT_MULTI_CB_CLASS,
            AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS,
            AbstractAttributeType::TYPE_OPT_SINGLE_RADIO_CLASS,
            AbstractAttributeType::TYPE_OPT_SINGLE_SELECT_CLASS,
            AbstractAttributeType::TYPE_IMAGE_CLASS,
            AbstractAttributeType::TYPE_FILE_CLASS,
            AbstractAttributeType::TYPE_METRIC_CLASS,
            'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\BooleanType'
        );
        if (
            in_array($productAttribute->getAttributeType(), $attributeType)
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
     * Validation rule for the default value
     *
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    protected static function isDefaultValueValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        if ($productAttribute->getDefaultValue()) {
            $path = preg_split('/[^[:alnum:]]/', $productAttribute->getAttributeType());
            $type = end($path);

            $exclusions = array(
                'OptionMultiCheckboxType',
                'OptionMultiSelectType',
                'FileType',
                'ImageType',
            );

            if (in_array($type, $exclusions)) {
                $context->addViolation(self::VIOLATION_DEFAULT_VALUE_DISABLED);
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
                'OptionSimpleRadioType',
                'OptionSimpleSelectType',
        );

        $path = preg_split('/[^[:alnum:]]/', $productAttribute->getAttributeType());
        $type = end($path);

        if (in_array($type, $optionTypes)) {
            foreach ($productAttribute->getOptions() as $option) {
                if ($option->getDefaultValue() === null) {
                    $context->addViolation(self::VIOLATION_OPTION_DEFAULT_VALUE_REQUIRED);
                }
            }
        }
    }
}
