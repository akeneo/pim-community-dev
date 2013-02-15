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
     * Classes for AttributeType
     * @staticvar string
     */
    const TYPE_DATE              = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType';
    const TYPE_INTEGER           = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\IntegerType';
    const TYPE_MONEY             = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType';
    const TYPE_NUMBER            = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\NumberType';
    const TYPE_OPT_MULTI_CB      = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType';
    const TYPE_OPT_MULTI_SELECT  = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType';
    const TYPE_OPT_SINGLE_RADIO  = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleRadioType';
    const TYPE_OPT_SINGLE_SELECT = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType';
    const TYPE_TEXTAREA          = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType';
    const TYPE_METRIC            = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType';
    const TYPE_FILE              = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileType';
    const TYPE_IMAGE             = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageType';

    const TYPE_TEXT              = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType';


    /**
     * Violation messages for unique attribute
     * @staticvar string
     */
    const VIOLATION_UNIQUE = 'Unique attribute results in used of Global scope and no translations';

    /**
     * Validate ProductAttribute entity
     * @param ProductAttribute $productAttribute ProductAttirbute entity
     * @param ExecutionContext $context          Execution context
     *
     * @static
     */
    public static function isValid(ProductAttribute $productAttribute, ExecutionContext $context)
    {
        self::isAttributeTypeMatrixValid($productAttribute, $context);
        self::isUniqueConstraintValid($productAttribute, $context);
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
        switch ($productAttribute->getAttributeType()) {
            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
            case self::TYPE_MONEY:
            case self::TYPE_OPT_MULTI_CB:
            case self::TYPE_OPT_MULTI_SELECT:
            case self::TYPE_OPT_SINGLE_RADIO:
            case self::TYPE_OPT_SINGLE_SELECT:
                // translatable and unique must be disabled
                if ($productAttribute->getTranslatable() === true || $productAttribute->getUnique() === true) {
                    $context->addViolation(
                        'For this attribute type value, translatable and unique values must be false'
                    );
                }
                break;
            case self::TYPE_TEXTAREA:
                // unique must be disabled
                if ($productAttribute->getUnique() === true) {
                    $context->addViolation('For this attribute type value, unique value must be false');
                }
                break;
            case self::TYPE_DATE:
                // translatable must be disabled
                if ($productAttribute->getTranslatable() === true) {
                    $context->addViolation('For this attribute type value, translatable value must be false');
                }
                break;
            case self::TYPE_IMAGE:
            case self::TYPE_FILE:
                // searchable and smart must be disabled
                if ($productAttribute->getSearchable() === true || $productAttribute->getSmart() === true) {
                    $context->addViolation('For this attribute type value, searchable and smart values must be false');
                }
                break;
            case self::TYPE_METRIC:
                // unique must be disabled
                if ($productAttribute->getUnique() === true
                    || $productAttribute->getTranslatable() === true
                    || $productAttribute->getScopable() != false) {
                    $context->addViolation(
                        'For this attribute type, unique and translatable values must be false. Scope must be global'
                    );
                }
                break;
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
                $context->addViolation(self::VIOLATION_UNIQUE);
            }
        }
    }
}
