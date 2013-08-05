<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validator for ProductAttrkbute not being translatable and scopable when unique
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueMatrixValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity->getUnique() === true) {
            if ($entity->getScopable()) {
                $this->context->addViolationAt('scopable', $constraint->scopableMessage);
            }
            if ($entity->getTranslatable()) {
                $this->context->addViolationAt('translatable', $constraint->translatableMessage);
            }
        }
    }
}
