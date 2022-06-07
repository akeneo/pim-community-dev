<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\ManualUpload;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualStorage;
use Akeneo\Platform\JobAutomation\Infrastructure\Validation\FilePath;
use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\ManualUpload\ManualUploadStorage as ManualStorageConstraint;
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
                'type' => new EqualTo(ManualStorage::TYPE),
                'file_path' => new FilePath($constraint->getFilePathSupportedFileExtensions()),
            ],
        ]));
    }
}
