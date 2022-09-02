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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\UploadedFile as UploadedFileConstraint;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileValidatorTest extends AbstractValidationTest
{
    public function test_it_validates_that_uploaded_file_is_valid(): void
    {
        $uploadedFile = new UploadedFile(
            __DIR__ . '/../../../Common/simple_import.xlsx',
            'filename.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
        $violations = $this->getValidator()->validate($uploadedFile, new UploadedFileConstraint());

        $this->assertNoViolation($violations);
    }

    public function test_it_builds_violations_when_mimetype_is_invalid(): void
    {
        $uploadedFile = new UploadedFile(
            __DIR__ . '/UploadedFileValidatorTest.php',
            'UploadedFileValidatorTest.php',
            'text/plain',
            null,
            true,
        );
        $violations = $this->getValidator()->validate($uploadedFile, new UploadedFileConstraint());

        $this->assertHasValidationError('akeneo.tailored_import.validation.uploaded_file.not_allowed_mime_type', '[file]', $violations);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
