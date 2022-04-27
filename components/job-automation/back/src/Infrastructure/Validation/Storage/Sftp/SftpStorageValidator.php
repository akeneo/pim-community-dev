<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\FilePath;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Hostname;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SftpStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SftpStorage) {
            throw new UnexpectedTypeException($constraint, SftpStorage::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo('sftp'),
                'file_path' => new FilePath($constraint->getFilePathAllowedFileExtensions()),
                'host' => [new NotBlank(), new Hostname()],
                'port' => [new NotBlank(), new GreaterThanOrEqual(1), new LessThanOrEqual(65535)],
                'username' => new NotBlank(),
                'password' => new NotBlank(),
            ],
        ]));
    }
}
