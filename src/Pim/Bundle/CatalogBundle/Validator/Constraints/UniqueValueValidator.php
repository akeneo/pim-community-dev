<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validator for unique value constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueValidator extends ConstraintValidator
{
    /**
     * Constructor
     *
     * @param object $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Constraint is applied on ProductValue data property.
     * That's why we use the current property path to guess the code
     * of the attribute to which the data belongs to.
     * @param object     $value
     * @param Constraint $constraint
     *
     * @see Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser
     */
    public function validate($value, Constraint $constraint)
    {
        $entity = $this->getEntity();
        if (!$entity instanceof ProductValueInterface || empty($value)) {
            return;
        }

        $em = $this->registry->getManagerForClass(get_class($entity));
        $criteria = array(
            'attribute'                               => $entity->getAttribute(),
            $entity->getAttribute()->getBackendType() => $value,
        );
        $result = $em->getRepository(get_class($entity))->findBy($criteria);

        if (0 === count($result) ||
            (1 === count($result) && $entity === ($result instanceof \Iterator ? $result->current() : current($result)))
        ) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }

    /**
     * Get entity
     *
     * @return mixed|null
     */
    private function getEntity()
    {
        preg_match(
            '/children\[values\].children\[(\w+)\].children\[\w+\].data/',
            $this->context->getPropertyPath(),
            $matches
        );
        if (!isset($matches[1])) {
            return;
        }

        $product = $this->context->getRoot()->getData();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (false === $entity = $product->getValue($matches[1])) {
            return;
        }

        return $entity;
    }
}
