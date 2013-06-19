<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueValidator extends ConstraintValidator
{
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function validate($value, Constraint $constraint)
    {
        (var_dump($this->context->getCurrentClass()));
        die(var_dump($value));
        if (!$value instanceof ProductValue) {
            return;
        }

        $em = $this->registry->getManagerForClass(get_class($entity));
        $criteria = array(
            'attribute'                              => $value->getAttribute(),
            $value->getAttribute()->getBackendType() => $value->getData(),
        );
        die(var_dump($criteria));
        $result = $em->getRepository($this->context->getCurrentClass())->findBy($criteria);

        if (0 === count($result) || (1 === count($result) && $entity === ($result instanceof \Iterator ? $result->current() : current($result)))) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}

