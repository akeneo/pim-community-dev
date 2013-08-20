<?php
namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Valid default value validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidDefaultValueValidator extends ConstraintValidator
{
    /**
     * @param mixed      $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        $value = $entity->getDefaultValue();
        if ($value === null) {
            return;
        }

        switch ($entity->getAttributeType()) {
            case 'pim_product_date':
                if (!$value instanceof \Datetime) {
                    $message = $constraint->dateFormatMessage;
                    break;
                }
                if ($min = $entity->getDateMin()) {
                    if ($min->getTimestamp() > $value->getTimestamp()) {
                        $message = $constraint->dateMessage;
                        break;
                    }
                }
                if ($max = $entity->getDateMax()) {
                    if ($max->getTimestamp() < $value->getTimestamp()) {
                        $message = $constraint->dateMessage;
                        break;
                    }
                }

                return;
            case 'pim_product_price_collection':
            case 'pim_product_number':
            case 'pim_product_metric':
                if ($entity->isNegativeAllowed() === false && $value < 0) {
                    $message = $constraint->negativeMessage;
                    break;
                }
                if ($entity->getNumberMin() !== null && $value < $entity->getNumberMin()) {
                    $message = $constraint->numberMessage;
                    break;
                }
                if ($entity->getNumberMax() !== null && $value > $entity->getNumberMax()) {
                    $message = $constraint->numberMessage;
                    break;
                }
                if ($entity->isDecimalsAllowed() === false && $value != (int) $value) {
                    $message = $constraint->decimalsMessage;
                    break;
                }

                return;
            case 'pim_product_text':
            case 'pim_product_textarea':
                if ($entity->getMaxCharacters() !== null) {
                    if (strlen($value) > $entity->getMaxCharacters()) {
                        $message = $constraint->charactersMessage;
                        break;
                    }
                }

                if ($entity->getAttributeType() === 'pim_product_text' && $entity->getValidationRule() == 'regexp') {
                    if (@preg_match($entity->getValidationRegexp(), $value)) {
                        return;
                    }
                    $message = $constraint->regexpMessage;
                    break;
                }

                return;
            default:
                return;
        }

        $this->context->addViolationAt($constraint->propertyPath, $message);
    }
}
