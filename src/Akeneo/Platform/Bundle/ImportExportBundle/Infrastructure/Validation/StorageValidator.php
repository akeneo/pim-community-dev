<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StorageValidator extends ConstraintValidator
{
    private array $storageConstraints;

    public function __construct(iterable $storageConstraints)
    {
        $this->storageConstraints = $storageConstraints instanceof \Traversable
            ? iterator_to_array($storageConstraints)
            : $storageConstraints;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Storage) {
            throw new UnexpectedTypeException($constraint, Storage::class);
        }

        if (!in_array($value['type'], $this->getStorageTypes())) {
            $this->context->buildViolation(
                Storage::UNAVAILABLE_TYPE,
                [
                    '{{ available_types }}' => implode(', ', $this->getStorageTypes()),
                ],
            )
                ->atPath('[type]')
                ->addViolation();

            return;
        }

        $this->validateStorageByType($value, $constraint->getFilePathSupportedFileExtensions());
    }

    private function getStorageTypes(): array
    {
        return array_keys($this->storageConstraints);
    }

    private function validateStorageByType(array $storage, array $allowedExtensions): void
    {
        $storageType = $storage['type'];
        $storageConstraint = $this->storageConstraints[$storageType] ?? null;

        if (!$storageConstraint instanceof StorageConstraint) {
            return;
        }

        $storageConstraint->setFilePathSupportedFileExtensions($allowedExtensions);

        $this->context->getValidator()->inContext($this->context)->validate($storage, $storageConstraint);
    }
}
