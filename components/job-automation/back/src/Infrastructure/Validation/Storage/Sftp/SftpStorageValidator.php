<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\FilePath;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\SftpStorage as SftpStorageModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Hostname;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SftpStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SftpStorage) {
            throw new UnexpectedTypeException($constraint, SftpStorage::class);
        }

        $validator = $this->context->getValidator()->inContext($this->context);

        $validator->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(SftpStorageModel::TYPE),
                'file_path' => new FilePath($constraint->getFilePathSupportedFileExtensions()),
                'host' => [new NotBlank(), new Hostname()],
                'fingerprint' => new Optional(new Fingerprint()),
                'port' => [new NotBlank(), new GreaterThanOrEqual(1), new LessThanOrEqual(65535)],
                'login_type' => [new NotBlank(), new Choice(['choices' => SftpStorageModel::LOGIN_TYPES])],
                'username' => new NotBlank(),
                'password' => new Optional(),
            ],
        ]));

        if (SftpStorageModel::LOGIN_TYPE_PASSWORD === $value['login_type']) {
            $validator->atPath('[password]')->validate($value['password'] ?? null, [
                new NotBlank(),
            ]);
        }
    }
}
