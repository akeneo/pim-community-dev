<?php

declare(strict_types=1);

namespace AkeneoTest\Acceptance\Common;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryUniqueEntityValidator extends ConstraintValidator
{
    private PropertyAccessor $propertyAccessor;
    private array $repositories = [];

    public function __construct(PropertyAccessor $propertyAccessor, array $repositories)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->repositories = $repositories;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, UniqueEntity::class);
        }

        if (!\is_array($constraint->fields) && !\is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        $uniqueFields = (array) $constraint->fields;
        $objectClass = \get_class($value);

        foreach ($this->getAllObjects($objectClass) as $existingEntity) {
            if ($existingEntity === $value) {
                continue;
            }
            $isUnique = true;
            foreach ($uniqueFields as $uniqueField) {
                if ($this->propertyAccessor->getValue($existingEntity, $uniqueField) !== $this->propertyAccessor->getValue($value, $uniqueField)) {
                    $isUnique = false;
                }
            }
            if ($isUnique) {
                $this->context->buildViolation('not unique entity');

                return;
            }
        }
    }

    private function getAllObjects(string $objectClass): array
    {
        $repo = $this->repositories[$objectClass] ?? null;
        if (null === $repo) {
            // If the repository is not injected, we don't validate.
            return [];
        }

        return $repo->findAll();
    }
}
