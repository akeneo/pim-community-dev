<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Check that an entity does not already exist, here we don't that those objects (done by Symfony)
 * are equals but we check that their identifier are equals.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueEntityValidator extends ConstraintValidator
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, UniqueEntity::class);
        }

        if (null === $constraint->entityClass) {
            throw new InvalidArgumentException('You need to provide a valid entity class');
        }

        if (!$entity instanceof $constraint->entityClass) {
            throw new UnexpectedTypeException($constraint, $constraint->entityClass);
        }

        $repository = $this->objectManager->getRepository($constraint->entityClass);
        $getter = sprintf('get%s', ucfirst($constraint->identifier));

        if (null === $entityInDatabase = $repository->findOneBy([$constraint->identifier => $entity->$getter()])) {
            return;
        }

        // here this is an update, we need to update the same object
        if ($entity->getId() !== $entityInDatabase->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->identifier)
                ->addViolation();
        }
    }
}
