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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\AmazonS3;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\FilePath;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\AmazonS3Storage as AmazonS3StorageModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AmazonS3StorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AmazonS3Storage) {
            throw new UnexpectedTypeException($constraint, AmazonS3Storage::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(AmazonS3StorageModel::TYPE),
                'file_path' => new FilePath($constraint->getFilePathSupportedFileExtensions()),
                'region' => new NotBlank(),
                'bucket' => new NotBlank(),
                'key' => new NotBlank(),
                'secret' => new NotBlank(),
            ],
        ]));
    }
}
