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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation\GetFileTemplateInformationQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsValidFileTemplateInformationQueryValidator extends ConstraintValidator
{
    public function __construct(
        private FilesystemProvider $filesystemProvider,
        private XlsxFileReaderFactoryInterface $fileReaderFactory,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof GetFileTemplateInformationQuery) {
            throw new UnexpectedTypeException($value, GetFileTemplateInformationQuery::class);
        }

        if (!$constraint instanceof IsValidFileTemplateInformationQuery) {
            throw new UnexpectedTypeException($constraint, IsValidFileTemplateInformationQuery::class);
        }

        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        if (!$fileSystem->fileExists($value->fileKey)) {
            $this->context->buildViolation(IsValidFileTemplateInformationQuery::FILE_NOT_FOUND)
                ->atPath('[file_key]')
                ->addViolation();

            return;
        }

        $this->validateSheetExist($value);
    }

    public function validateSheetExist(GetFileTemplateInformationQuery $query): void
    {
        if (null === $query->sheetName) {
            return;
        }

        $fileReader = $this->fileReaderFactory->create($query->fileKey);
        $sheetNames = $fileReader->getSheetNames();

        if (!in_array($query->sheetName, $sheetNames)) {
            $this->context->buildViolation(IsValidFileTemplateInformationQuery::SHEET_NOT_FOUND)
                ->atPath('[sheet_name]')
                ->addViolation();
        }
    }
}
