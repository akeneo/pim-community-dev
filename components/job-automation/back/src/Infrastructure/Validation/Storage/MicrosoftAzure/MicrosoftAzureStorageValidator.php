<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\MicrosoftAzure;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\FilePath;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\MicrosoftAzureStorage as MicrosoftAzureModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MicrosoftAzureStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MicrosoftAzureStorage) {
            throw new UnexpectedTypeException($constraint, MicrosoftAzureStorage::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(MicrosoftAzureModel::TYPE),
                'file_path' => new FilePath($constraint->getFilePathSupportedFileExtensions()),
                'connection_string' => new NotBlank(),
                'container_name' => new NotBlank(),
            ],
        ]));
    }
}
