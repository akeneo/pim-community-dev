<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeAttributeValidator extends ConstraintValidator
{
    private const INVALID_CODE_LIST = ['entity_type'];

    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint): void
    {
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if ($this->isValidCode($attribute)) {
            $this->context
                ->buildViolation($constraint->messageAttributeCode)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }

    /**
     * Check if the code is valid.
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    protected function isValidCode(AttributeInterface $attribute): bool
    {
        return in_array($attribute->getCode(), self::INVALID_CODE_LIST);
    }
}
