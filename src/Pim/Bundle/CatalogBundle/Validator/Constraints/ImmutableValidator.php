<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityManager;

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
     * @var EntityManager $em
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
     * @param Locale     $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        $originalData = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
        $accessor     = PropertyAccess::createPropertyAccessor();

        foreach ($constraint->properties as $property) {
            $originalValue = $accessor->getValue($originalData, sprintf('[%s]', $property));
            if (null !== $originalValue) {
                $newValue = $accessor->getValue($entity, $property);

                if ($originalValue !== $newValue) {
                    $this->context->addViolationAt($property, $constraint->message);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
