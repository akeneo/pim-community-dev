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
            case AbstractAttributeType::TYPE_INTEGER_CLASS:
            case AbstractAttributeType::TYPE_NUMBER_CLASS:
            case AbstractAttributeType::TYPE_MONEY_CLASS:
            case AbstractAttributeType::TYPE_OPT_MULTI_CB_CLASS:
            case AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS:
            case AbstractAttributeType::TYPE_OPT_SINGLE_RADIO_CLASS:
            case AbstractAttributeType::TYPE_OPT_SINGLE_SELECT_CLASS:
                // translatable and unique must be disabled
                if ($productAttribute->getTranslatable() === true || $productAttribute->getUnique() === true) {
                    $context->addViolation(
                        'For this attribute type value, translatable and unique values must be false'
                    );
                }
                break;
            case AbstractAttributeType::TYPE_TEXTAREA_CLASS:
                // unique must be disabled
                if ($productAttribute->getUnique() === true) {
                    $context->addViolation('For this attribute type value, unique value must be false');
                }
                break;
            case AbstractAttributeType::TYPE_DATE_CLASS:
                // translatable must be disabled
                if ($productAttribute->getTranslatable() === true) {
                    $context->addViolation('For this attribute type value, translatable value must be false');
                }
                break;
            case AbstractAttributeType::TYPE_IMAGE_CLASS:
            case AbstractAttributeType::TYPE_FILE_CLASS:
                // searchable and smart must be disabled
                if ($productAttribute->getSearchable() === true || $productAttribute->getSmart() === true) {
                    $context->addViolation('For this attribute type value, searchable and smart values must be false');
                }
                break;
            case AbstractAttributeType::TYPE_METRIC_CLASS:
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
