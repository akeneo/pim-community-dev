<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\StorageConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StorageValidator extends ConstraintValidator
{
    public function __construct(
        /** @var array<string, string> */
        private array $storageConstraintClasses,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Storage) {
            throw new UnexpectedTypeException($constraint, Storage::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new Choice($this->getStorageTypes()),
            ],
            'allowExtraFields' => true,
        ]));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $this->validateStorageByType($value, $constraint->getFilePathAllowedFileExtensions());
    }

    private function getStorageTypes(): array
    {
        return array_keys($this->storageConstraintClasses);
    }

    private function validateStorageByType(array $storage, array $allowedExtensions): void
    {
        $storageType = $storage['type'];
        $storageConstraintClass = $this->storageConstraintClasses[$storageType] ?? null;

        if (null === $storageConstraintClass || !$this->isStorageConstraint($storageConstraintClass)) {
            return;
        }

        $this->context->getValidator()->inContext($this->context)->validate($storage, new $storageConstraintClass($allowedExtensions));
    }

    private function isStorageConstraint(string $storageConstraintClass): bool
    {
        return is_subclass_of($storageConstraintClass, StorageConstraint::class, true);
    }
}
