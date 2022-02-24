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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\FileKey;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileKeyValidatorTest extends AbstractValidationTest
{
    public function test_it_validates_that_file_key_is_valid_when_null(): void
    {
        $violations = $this->getValidator()->validate(null, new FileKey());

        $this->assertNoViolation($violations);
    }

    public function test_it_validates_that_file_exists(): void
    {
        $fileKey = $this->uploadFile();
        $violations = $this->getValidator()->validate($fileKey, new FileKey());

        $this->assertNoViolation($violations);
    }

    public function test_it_builds_violations_when_file_key_is_invalid(): void
    {
        $violations = $this->getValidator()->validate('', new FileKey());

        $this->assertHasValidationError('This value should not be blank.', '', $violations);
    }

    public function test_it_builds_violations_when_file_is_not_found(): void
    {
        $violations = $this->getValidator()->validate('unknown_key', new FileKey());

        $this->assertHasValidationError('akeneo.tailored_import.validation.file_key.file_does_not_exist', '', $violations);
    }

    private function uploadFile(): string
    {
        $uploadedFile = new UploadedFile(__DIR__ . '/../../../Common/simple_import.xlsx', 'filename.xlsx');
        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($uploadedFile, Storage::FILE_STORAGE_ALIAS);

        return $file->getKey();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
