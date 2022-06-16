<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\ManualUpload;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\FilePath;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\ManualUpload\ManualUploadStorage as ManualStorageConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ManualUploadStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ManualStorageConstraint) {
            throw new UnexpectedTypeException($constraint, ManualStorageConstraint::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(ManualUploadStorage::TYPE),
                'file_path' => new FilePath($constraint->getFilePathSupportedFileExtensions()),
            ],
        ]));
    }
}
