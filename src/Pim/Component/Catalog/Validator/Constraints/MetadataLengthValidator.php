<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate that the length of a property is not longer that its field in the database
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetadataLengthValidator extends ConstraintValidator
{
    /** @var EntityManagerInterface $em */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || $value === '') {
            return;
        }

        $stringValue = (string) $value;
        $valueLength = strlen($stringValue);
        $object = $this->context->getObject();

        if (is_object($object)) {
            $classMetadata = $this->em->getClassMetadata(ClassUtils::getClass($object));
            $propertyMetadata = $this->context->getMetadata()->getPropertyName();

            $fieldMapping = $classMetadata->getFieldMapping($propertyMetadata);
            if ($valueLength > $fieldMapping['length']) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ limit }}', $fieldMapping['length'])
                    ->setInvalidValue($value)
                    ->addViolation();
            }
        }
    }
}
