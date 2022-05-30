<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Manual;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualStorage as ManualStorageModel;
use Akeneo\Platform\JobAutomation\Infrastructure\Validation\FilePath;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ManualStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ManualStorage) {
            throw new UnexpectedTypeException($constraint, ManualStorage::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(ManualStorageModel::TYPE),
                'file_path' => new FilePath($constraint->getFilePathSupportedFileExtensions()),
            ],
        ]));
    }
}
