<?php

namespace Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the immutable constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImmutableValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Validate the property
     *
     * @param object     $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        $originalData = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($constraint->properties as $property) {
            $originalValue = $this->getOriginalValue($accessor, $originalData, $property);
            if (null !== $originalValue) {
                $newValue = $this->getNewValue($accessor, $entity, $property);
                $isDifferent = $originalValue !== $newValue;
                $isDirtyCollection = ($newValue instanceof PersistentCollection && $newValue->isDirty());
                if ($isDifferent || $isDirtyCollection) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath($property)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param  PropertyAccessor $accessor
     * @param  mixed            $originalData
     * @param  string           $property
     *
     * @return mixed
     */
    private function getOriginalValue(PropertyAccessor $accessor, $originalData, string $property)
    {
        $originalValue = $accessor->getValue($originalData, sprintf('[%s]', $property));
        if (null === $originalValue) {
            $originalValue = $accessor->getValue($originalData, sprintf('[properties][%s]', $property));
        }

        return $originalValue;
    }

    /**
     * @param  PropertyAccessor $accessor
     * @param  mixed            $entity
     * @param  string           $property
     *
     * @return mixed
     */
    private function getNewValue(PropertyAccessor $accessor, $entity, string $property)
    {
        $newValue = $accessor->getValue($entity, $property);
        if (null === $newValue) {
            $newValue = $accessor->getValue($entity, sprintf('properties[%s]', $property));
        }

        return $newValue;
    }
}
