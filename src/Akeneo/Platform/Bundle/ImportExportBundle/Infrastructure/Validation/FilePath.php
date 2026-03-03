<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class FilePath extends Constraint
{
    public const UNSUPPORTED_EXTENSION = 'pim_import_export.form.job_instance.validation.file_path.unsupported_extension';
    public const NON_PRINTABLE_FILE_PATH = 'pim_import_export.form.job_instance.validation.file_path.non_printable_filepath';

    public function __construct(
        /** @var string[] */
        private array $supportedFileExtensions,
    ) {
        parent::__construct();
    }

    public function getSupportedFileExtensions(): array
    {
        return $this->supportedFileExtensions;
    }
}
